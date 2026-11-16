<?php

namespace App\Controllers;

use App\Models\FacturaModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\RedirectResponse;
use DateTimeImmutable;

class Facturas extends BaseController
{
    private const ESTADO_INICIAL = 'Pendiente';

    private function currentUserId(): int
    {
        $userId = session()->get('user_id');

        return is_numeric($userId) ? (int) $userId : 0;
    }

    private function serviceAssociationsForCurrentUser(): array
    {
        return db_connect()
            ->table('inmueble_servicios isv')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->join('direcciones d', 'd.id = i.direccion_id')
            ->join('servicios s', 's.id = isv.servicio_id')
            ->where('i.usuario_id', $this->currentUserId())
            ->select([
                'isv.id',
                'isv.estado AS estado_asociacion',
                'i.alias_nombre',
                'i.tipo AS tipo_inmueble',
                'd.calle',
                'd.numero',
                'd.ciudad',
                's.nombre AS servicio_nombre',
                's.proveedor',
                's.unidad_medida_std',
            ])
            ->orderBy('i.alias_nombre', 'ASC')
            ->orderBy('s.nombre', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function submittedFacturaData(): array
    {
        $submittedData = [];

        foreach ([
            'inmueble_servicio_id',
            'periodo_facturacion',
            'fecha_emision',
            'fecha_primer_vencimiento',
            'fecha_segundo_vencimiento',
            'monto_total',
            'consumo_periodo',
            'lectura_anterior',
            'lectura_actual',
        ] as $field) {
            $value = $this->request->getPost($field);
            $submittedData[$field] = is_scalar($value) ? trim((string) $value) : '';
        }

        return $submittedData;
    }

    private function facturaValidationRules(): array
    {
        return [
            'inmueble_servicio_id'       => 'required|is_natural_no_zero',
            'periodo_facturacion'        => 'required',
            'fecha_emision'              => 'required|valid_date[Y-m-d]',
            'fecha_primer_vencimiento'   => 'required|valid_date[Y-m-d]',
            'fecha_segundo_vencimiento'  => 'permit_empty|valid_date[Y-m-d]',
            'monto_total'                => 'required|decimal|greater_than[0]|less_than_equal_to[9999999999.99]',
            'consumo_periodo'            => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[999999999.999]',
            'lectura_anterior'           => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[999999999.999]',
            'lectura_actual'             => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[999999999.999]',
        ];
    }

    private function validatePeriodAndRelatedFields(array $data, array $errors): array
    {
        if (
            $data['periodo_facturacion'] !== ''
            && preg_match('/\A\d{4}-(0[1-9]|1[0-2])\z/', $data['periodo_facturacion']) !== 1
        ) {
            $errors['periodo_facturacion'] = 'Ingresá un período válido en formato mes y año.';
        }

        if (
            $data['fecha_segundo_vencimiento'] !== ''
            && ! isset($errors['fecha_segundo_vencimiento'])
            && ! isset($errors['fecha_primer_vencimiento'])
            && $data['fecha_segundo_vencimiento'] < $data['fecha_primer_vencimiento']
        ) {
            $errors['fecha_segundo_vencimiento'] = 'El segundo vencimiento no puede ser anterior al primero.';
        }

        $tieneLecturaAnterior = $data['lectura_anterior'] !== '';
        $tieneLecturaActual = $data['lectura_actual'] !== '';

        if ($tieneLecturaAnterior !== $tieneLecturaActual) {
            $errors['lectura_actual'] = 'Completá ambas lecturas o dejá las dos vacías.';
        } elseif (
            $tieneLecturaAnterior
            && ! isset($errors['lectura_anterior'])
            && ! isset($errors['lectura_actual'])
            && (float) $data['lectura_actual'] < (float) $data['lectura_anterior']
        ) {
            $errors['lectura_actual'] = 'La lectura actual no puede ser menor que la anterior.';
        }

        return $errors;
    }

    private function redirectWithValidationErrors(array $data, array $errors): RedirectResponse
    {
        return redirect()->to(site_url('facturas'))
            ->withInput()
            ->with('facturaErrors', $errors)
            ->with('facturaFormData', $data);
    }

    private function invoiceAlreadyExists(int $associationId, string $period): bool
    {
        $periodStart = $period . '-01';
        $periodEnd = (new DateTimeImmutable($periodStart))->modify('first day of next month')->format('Y-m-d');

        return db_connect()
            ->table('facturas')
            ->where('inmueble_servicio_id', $associationId)
            ->where('periodo_facturacion >=', $periodStart)
            ->where('periodo_facturacion <', $periodEnd)
            ->countAllResults() > 0;
    }

    public function index(): string
    {
        return view('facturas/index', [
            'asociaciones' => $this->serviceAssociationsForCurrentUser(),
            'formData'     => session()->getFlashdata('facturaFormData') ?? [],
            'errors'       => session()->getFlashdata('facturaErrors') ?? [],
            'success'      => session()->getFlashdata('facturaSuccess'),
            'error'        => session()->getFlashdata('facturaError'),
        ]);
    }

    public function create(): RedirectResponse
    {
        $submittedData = $this->submittedFacturaData();
        $valid = $this->validateData($submittedData, $this->facturaValidationRules(), [
            'inmueble_servicio_id' => [
                'required'           => 'Seleccioná un servicio asociado.',
                'is_natural_no_zero' => 'Seleccioná un servicio válido.',
            ],
            'periodo_facturacion' => [
                'required' => 'Ingresá el período de facturación.',
            ],
            'fecha_emision' => [
                'required' => 'Ingresá la fecha de emisión.',
                'valid_date' => 'Ingresá una fecha de emisión válida.',
            ],
            'fecha_primer_vencimiento' => [
                'required' => 'Ingresá la fecha del primer vencimiento.',
                'valid_date' => 'Ingresá una fecha de primer vencimiento válida.',
            ],
            'fecha_segundo_vencimiento' => [
                'valid_date' => 'Ingresá una fecha de segundo vencimiento válida.',
            ],
            'monto_total' => [
                'required' => 'Ingresá el monto de la factura.',
                'decimal' => 'Ingresá un monto numérico válido.',
                'greater_than' => 'El monto debe ser mayor que cero.',
                'less_than_equal_to' => 'El monto supera el máximo permitido.',
            ],
            'consumo_periodo' => [
                'required' => 'Ingresá el consumo del período.',
                'decimal' => 'Ingresá un consumo numérico válido.',
                'greater_than_equal_to' => 'El consumo no puede ser negativo.',
                'less_than_equal_to' => 'El consumo supera el máximo permitido.',
            ],
            'lectura_anterior' => [
                'decimal' => 'Ingresá una lectura anterior numérica válida.',
                'greater_than_equal_to' => 'La lectura anterior no puede ser negativa.',
                'less_than_equal_to' => 'La lectura anterior supera el máximo permitido.',
            ],
            'lectura_actual' => [
                'decimal' => 'Ingresá una lectura actual numérica válida.',
                'greater_than_equal_to' => 'La lectura actual no puede ser negativa.',
                'less_than_equal_to' => 'La lectura actual supera el máximo permitido.',
            ],
        ]);

        $errors = $valid ? [] : $this->validator->getErrors();
        $errors = $this->validatePeriodAndRelatedFields($submittedData, $errors);

        if ($errors !== []) {
            return $this->redirectWithValidationErrors($submittedData, $errors);
        }

        $associationId = (int) $submittedData['inmueble_servicio_id'];
        $association = db_connect()
            ->table('inmueble_servicios isv')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->where('isv.id', $associationId)
            ->where('i.usuario_id', $this->currentUserId())
            ->countAllResults();

        if ($association === 0) {
            return $this->redirectWithValidationErrors($submittedData, [
                'inmueble_servicio_id' => 'El servicio seleccionado no existe o no pertenece a tus inmuebles.',
            ]);
        }

        if ($this->invoiceAlreadyExists($associationId, $submittedData['periodo_facturacion'])) {
            return $this->redirectWithValidationErrors($submittedData, [
                'periodo_facturacion' => 'Ya existe una factura para ese servicio y período.',
            ]);
        }

        $periodo = $submittedData['periodo_facturacion'] . '-01';
        $invoiceData = [
            'inmueble_servicio_id'      => $associationId,
            'periodo_facturacion'       => $periodo,
            'fecha_emision'             => $submittedData['fecha_emision'],
            'fecha_primer_vencimiento'  => $submittedData['fecha_primer_vencimiento'],
            'fecha_segundo_vencimiento' => $submittedData['fecha_segundo_vencimiento'] ?: null,
            'monto_total'               => $submittedData['monto_total'],
            'consumo_periodo'           => $submittedData['consumo_periodo'],
            'lectura_anterior'          => $submittedData['lectura_anterior'] !== '' ? $submittedData['lectura_anterior'] : null,
            'lectura_actual'            => $submittedData['lectura_actual'] !== '' ? $submittedData['lectura_actual'] : null,
            'estado_factura'            => self::ESTADO_INICIAL,
        ];

        try {
            $inserted = (new FacturaModel())->insert($invoiceData);
        } catch (DatabaseException $exception) {
            if ($this->invoiceAlreadyExists($associationId, $submittedData['periodo_facturacion'])) {
                return $this->redirectWithValidationErrors($submittedData, [
                    'periodo_facturacion' => 'Ya existe una factura para ese servicio y período.',
                ]);
            }

            log_message('error', 'No se pudo guardar la factura: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('facturas'))
                ->with('facturaFormData', $submittedData)
                ->with('facturaError', 'No se pudo guardar la factura. Revisá los datos e intentá nuevamente.');
        }

        if (! $inserted) {
            log_message('error', 'No se pudo guardar la factura por un error de validación del modelo o de persistencia.');

            return redirect()->to(site_url('facturas'))
                ->with('facturaFormData', $submittedData)
                ->with('facturaError', 'No se pudo guardar la factura. Revisá los datos e intentá nuevamente.');
        }

        return redirect()->to(site_url('facturas'))
            ->with('facturaSuccess', 'La factura se registró correctamente como pendiente.');
    }
}

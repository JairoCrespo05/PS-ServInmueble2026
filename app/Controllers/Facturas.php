<?php

namespace App\Controllers;

use App\Models\FacturaModel;
use App\Models\InmuebleServicioModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Exceptions\PageNotFoundException;
use DateTimeImmutable;

class Facturas extends BaseController
{
    private const ESTADO_PENDIENTE = 'Pendiente';
    private const ESTADO_PAGADA = 'Pagada';
    private const MEDIOS_PAGO = [
        'Transferencia',
        'Débito Automático',
        'Efectivo',
        'Billetera Virtual',
    ];
    private const PER_PAGE = 15;

    private function currentUserId(): int
    {
        $userId = session()->get('user_id');

        return is_numeric($userId) ? (int) $userId : 0;
    }

    private function ownedInvoicesQuery(bool $selectDetails = true): BaseBuilder
    {
        $query = db_connect()
            ->table('facturas f')
            ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->join('direcciones d', 'd.id = i.direccion_id')
            ->join('servicios s', 's.id = isv.servicio_id')
            ->where('i.usuario_id', $this->currentUserId());

        if ($selectDetails) {
            $query->select([
                'f.id AS factura_id',
                'f.inmueble_servicio_id',
                'f.periodo_facturacion',
                'f.fecha_emision',
                'f.fecha_primer_vencimiento',
                'f.fecha_segundo_vencimiento',
                'f.monto_total',
                'f.consumo_periodo',
                'f.lectura_anterior',
                'f.lectura_actual',
                'f.estado_factura',
                'f.fecha_pago_realizado',
                'f.monto_abonado',
                'f.medio_pago',
                'i.id AS inmueble_id',
                'i.alias_nombre',
                'i.tipo AS tipo_inmueble',
                'd.calle',
                'd.numero',
                'd.piso_dpto',
                'd.ciudad',
                's.nombre AS servicio_nombre',
                's.proveedor',
                's.unidad_medida_std',
            ]);
        }

        return $query;
    }

    private function serviceAssociationsForCurrentUser(): array
    {
        return (new InmuebleServicioModel())->facturablesParaPropietario($this->currentUserId())
            ->join('direcciones d', 'd.id = i.direccion_id')
            ->join('servicios s', 's.id = isv.servicio_id')
            ->select([
                'isv.id',
                'i.alias_nombre',
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

    private function submittedInvoiceData(): array
    {
        $data = [];

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
            $data[$field] = is_scalar($value) ? trim((string) $value) : '';
        }

        return $data;
    }

    private function invoiceValidationRules(): array
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

    private function validateInvoiceRelations(array $data, array $errors): array
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

        $hasPreviousReading = $data['lectura_anterior'] !== '';
        $hasCurrentReading = $data['lectura_actual'] !== '';

        if ($hasPreviousReading !== $hasCurrentReading) {
            $errors['lectura_actual'] = 'Completá ambas lecturas o dejá las dos vacías.';
        } elseif (
            $hasPreviousReading
            && ! isset($errors['lectura_anterior'])
            && ! isset($errors['lectura_actual'])
            && (float) $data['lectura_actual'] < (float) $data['lectura_anterior']
        ) {
            $errors['lectura_actual'] = 'La lectura actual no puede ser menor que la anterior.';
        }

        return $errors;
    }

    private function invoiceAlreadyExists(int $associationId, string $period): bool
    {
        $periodStart = $period . '-01';
        $periodEnd = (new DateTimeImmutable($periodStart))
            ->modify('first day of next month')
            ->format('Y-m-d');

        return db_connect()
            ->table('facturas')
            ->where('inmueble_servicio_id', $associationId)
            ->where('periodo_facturacion >=', $periodStart)
            ->where('periodo_facturacion <', $periodEnd)
            ->countAllResults() > 0;
    }

    private function redirectWithInvoiceErrors(array $data, array $errors): RedirectResponse
    {
        return redirect()->to(site_url('facturas/nueva'))
            ->withInput()
            ->with('invoiceErrors', $errors)
            ->with('invoiceFormData', $data);
    }

    public function index(): string
    {
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $query = $this->ownedInvoicesQuery();
        $total = (clone $query)->countAllResults();
        $pageCount = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $pageCount);

        $invoices = $query
            ->orderBy('f.periodo_facturacion', 'DESC')
            ->orderBy('f.id', 'DESC')
            ->limit(self::PER_PAGE, ($page - 1) * self::PER_PAGE)
            ->get()
            ->getResultArray();
        $pagerLinks = $total > self::PER_PAGE
            ? service('pager')->makeLinks($page, self::PER_PAGE, $total, 'default_full', 0, 'default')
            : '';

        return view('facturas/index', [
            'facturas' => $invoices,
            'pagerLinks' => $pagerLinks,
            'success' => session()->getFlashdata('invoiceSuccess'),
            'error' => session()->getFlashdata('invoiceError'),
        ]);
    }

    public function new(): string
    {
        return view('facturas/new', [
            'asociaciones' => $this->serviceAssociationsForCurrentUser(),
            'formData' => session()->getFlashdata('invoiceFormData') ?? [],
            'errors' => session()->getFlashdata('invoiceErrors') ?? [],
            'error' => session()->getFlashdata('invoiceError'),
        ]);
    }

    public function create(): RedirectResponse
    {
        $data = $this->submittedInvoiceData();
        $valid = $this->validateData($data, $this->invoiceValidationRules(), [
            'inmueble_servicio_id' => [
                'required' => 'Seleccioná un servicio asociado.',
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
        $errors = $this->validateInvoiceRelations($data, $errors);

        if ($errors !== []) {
            return $this->redirectWithInvoiceErrors($data, $errors);
        }

        $associationId = (int) $data['inmueble_servicio_id'];
        $association = (new InmuebleServicioModel())->facturablesParaPropietario($this->currentUserId())
            ->where('isv.id', $associationId)
            ->countAllResults();

        if ($association === 0) {
            return $this->redirectWithInvoiceErrors($data, [
                'inmueble_servicio_id' => 'El vínculo seleccionado no está activo, fue dado de baja o no corresponde a un inmueble habilitado tuyo.',
            ]);
        }

        if ($this->invoiceAlreadyExists($associationId, $data['periodo_facturacion'])) {
            return $this->redirectWithInvoiceErrors($data, [
                'periodo_facturacion' => 'Ya existe una factura para ese servicio y período.',
            ]);
        }

        $invoiceData = [
            'inmueble_servicio_id' => $associationId,
            'periodo_facturacion' => $data['periodo_facturacion'] . '-01',
            'fecha_emision' => $data['fecha_emision'],
            'fecha_primer_vencimiento' => $data['fecha_primer_vencimiento'],
            'fecha_segundo_vencimiento' => $data['fecha_segundo_vencimiento'] ?: null,
            'monto_total' => $data['monto_total'],
            'consumo_periodo' => $data['consumo_periodo'],
            'lectura_anterior' => $data['lectura_anterior'] !== '' ? $data['lectura_anterior'] : null,
            'lectura_actual' => $data['lectura_actual'] !== '' ? $data['lectura_actual'] : null,
            'estado_factura' => self::ESTADO_PENDIENTE,
        ];

        try {
            $inserted = (new FacturaModel())->insert($invoiceData);
        } catch (DatabaseException $exception) {
            if ($this->invoiceAlreadyExists($associationId, $data['periodo_facturacion'])) {
                return $this->redirectWithInvoiceErrors($data, [
                    'periodo_facturacion' => 'Ya existe una factura para ese servicio y período.',
                ]);
            }

            log_message('error', 'No se pudo guardar la factura: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('facturas/nueva'))
                ->with('invoiceFormData', $data)
                ->with('invoiceError', 'No se pudo guardar la factura. Revisá los datos e intentá nuevamente.');
        }

        if (! $inserted) {
            log_message('error', 'No se pudo guardar la factura por un error de validación del modelo o de persistencia.');

            return redirect()->to(site_url('facturas/nueva'))
                ->with('invoiceFormData', $data)
                ->with('invoiceError', 'No se pudo guardar la factura. Revisá los datos e intentá nuevamente.');
        }

        return redirect()->to(site_url('facturas'))
            ->with('invoiceSuccess', 'La factura se registró correctamente como pendiente.');
    }

    public function show(int $invoiceId): string
    {
        $invoice = $this->ownedInvoicesQuery()
            ->where('f.id', $invoiceId)
            ->get()
            ->getRowArray();

        if ($invoice === null) {
            throw PageNotFoundException::forPageNotFound('No se encontró la factura solicitada.');
        }

        return view('facturas/show', [
            'factura' => $invoice,
            'errors' => session()->getFlashdata('paymentErrors') ?? [],
            'formData' => session()->getFlashdata('paymentFormData') ?? [],
            'success' => session()->getFlashdata('paymentSuccess'),
            'error' => session()->getFlashdata('paymentError'),
            'mediosPago' => self::MEDIOS_PAGO,
        ]);
    }

    public function report(): string
    {
        $pending = (clone $this->ownedInvoicesQuery())
            ->where('f.estado_factura', self::ESTADO_PENDIENTE)
            ->orderBy('f.fecha_primer_vencimiento', 'ASC')
            ->orderBy('f.id', 'DESC')
            ->get()
            ->getResultArray();
        $paid = (clone $this->ownedInvoicesQuery())
            ->where('f.estado_factura', self::ESTADO_PAGADA)
            ->orderBy('f.fecha_pago_realizado', 'DESC')
            ->orderBy('f.id', 'DESC')
            ->get()
            ->getResultArray();
        $pendingTotal = $this->ownedInvoicesQuery(false)
            ->selectSum('f.monto_total', 'total')
            ->where('f.estado_factura', self::ESTADO_PENDIENTE)
            ->get()
            ->getRowArray()['total'] ?? '0.00';
        $paidTotal = $this->ownedInvoicesQuery(false)
            ->selectSum('f.monto_abonado', 'total')
            ->where('f.estado_factura', self::ESTADO_PAGADA)
            ->get()
            ->getRowArray()['total'] ?? '0.00';

        return view('facturas/report', [
            'pendientes' => $pending,
            'pagadas' => $paid,
            'totalPendiente' => (float) $pendingTotal,
            'totalPagado' => (float) $paidTotal,
        ]);
    }

    private function amountInCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    public function registerPayment(int $invoiceId): RedirectResponse
    {
        $paymentDate = $this->request->getPost('fecha_pago_realizado');
        $amount = $this->request->getPost('monto_abonado');
        $paymentMethod = $this->request->getPost('medio_pago');
        $data = [
            'fecha_pago_realizado' => is_scalar($paymentDate) ? trim((string) $paymentDate) : '',
            'monto_abonado' => is_scalar($amount) ? trim((string) $amount) : '',
            'medio_pago' => is_scalar($paymentMethod) ? trim((string) $paymentMethod) : '',
        ];
        $invoice = $this->ownedInvoicesQuery()
            ->where('f.id', $invoiceId)
            ->get()
            ->getRowArray();

        if ($invoice === null) {
            throw PageNotFoundException::forPageNotFound('No se encontró la factura solicitada.');
        }

        if ($invoice['estado_factura'] !== self::ESTADO_PENDIENTE) {
            return redirect()->to(site_url('facturas/' . $invoiceId))
                ->with('paymentError', 'Solo se puede registrar el pago de una factura pendiente.');
        }

        $valid = $this->validateData($data, [
            'fecha_pago_realizado' => 'required|valid_date[Y-m-d]',
            'monto_abonado' => 'required|decimal|greater_than[0]|less_than_equal_to[9999999999.99]',
            'medio_pago' => 'required|max_length[50]',
        ], [
            'fecha_pago_realizado' => [
                'required' => 'Ingresá la fecha del pago.',
                'valid_date' => 'Ingresá una fecha de pago válida.',
            ],
            'monto_abonado' => [
                'required' => 'Ingresá el monto abonado.',
                'decimal' => 'Ingresá un monto numérico válido.',
                'greater_than' => 'El monto abonado debe ser mayor que cero.',
                'less_than_equal_to' => 'El monto abonado supera el máximo permitido.',
            ],
            'medio_pago' => [
                'required' => 'Seleccioná el medio de pago.',
                'max_length' => 'El medio de pago no es válido.',
            ],
        ]);

        $errors = $valid ? [] : $this->validator->getErrors();
        if ($valid && ! in_array($data['medio_pago'], self::MEDIOS_PAGO, true)) {
            $errors['medio_pago'] = 'Seleccioná un medio de pago válido.';
        }
        if (
            $valid
            && ! isset($errors['monto_abonado'])
            && preg_match('/\A\d{1,10}(?:\.\d{1,2})?\z/', $data['monto_abonado']) !== 1
        ) {
            $errors['monto_abonado'] = 'Ingresá el importe con hasta dos decimales y sin separador de miles.';
        }
        if (
            $valid
            && ! isset($errors['fecha_pago_realizado'])
            && $data['fecha_pago_realizado'] > date('Y-m-d')
        ) {
            $errors['fecha_pago_realizado'] = 'La fecha de pago no puede ser futura.';
        }
        if (
            $valid
            && ! isset($errors['monto_abonado'])
            && $this->amountInCents($data['monto_abonado']) !== $this->amountInCents((string) $invoice['monto_total'])
        ) {
            $errors['monto_abonado'] = 'El pago debe cubrir el importe total de la factura. No se admiten pagos parciales.';
        }

        if ($errors !== []) {
            return redirect()->to(site_url('facturas/' . $invoiceId))
                ->withInput()
                ->with('paymentErrors', $errors)
                ->with('paymentFormData', $data);
        }

        try {
            $updated = db_connect()
                ->table('facturas')
                ->where('id', $invoiceId)
                ->where('estado_factura', self::ESTADO_PENDIENTE)
                ->update([
                    'estado_factura' => self::ESTADO_PAGADA,
                    'fecha_pago_realizado' => $data['fecha_pago_realizado'],
                    'monto_abonado' => $invoice['monto_total'],
                    'medio_pago' => $data['medio_pago'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        } catch (DatabaseException $exception) {
            log_message('error', 'No se pudo registrar el pago de la factura {id}: {message}', [
                'id' => $invoiceId,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->to(site_url('facturas/' . $invoiceId))
                ->with('paymentError', 'No se pudo registrar el pago. Intentá nuevamente.');
        }

        if (! $updated || db_connect()->affectedRows() !== 1) {
            return redirect()->to(site_url('facturas/' . $invoiceId))
                ->with('paymentError', 'La factura cambió de estado y no se pudo registrar el pago. Actualizá la página.');
        }

        return redirect()->to(site_url('facturas/' . $invoiceId))
            ->with('paymentSuccess', 'El pago total quedó registrado y la factura figura como pagada.');
    }
}

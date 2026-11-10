<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Inmuebles extends BaseController
{
    private function inmuebleValidationRules(): array
    {
        return [
            'alias_nombre' => 'required|max_length[150]',
            'tipo'         => 'required|max_length[100]|in_list[Casa,Departamento,Local comercial,Oficina,Galpón,local comercial,casa,departamento,oficina,galpón]',
            'superficie'   => 'required|decimal|greater_than[0]|less_than_equal_to[99999999.99]',
            'estado'       => 'required|in_list[Activo,Inactivo,Ocupado,Disponible,En Mantenimiento,ocupado,disponible,en mantenimiento]',
            'calle'        => 'required|max_length[150]',
            'numero'       => 'required|max_length[20]',
            'piso_dpto'    => 'permit_empty|max_length[50]',
            'ciudad'       => 'required|max_length[100]',
        ];
    }

    private function submittedInmuebleData(): array
    {
        $submittedData = [];

        foreach (array_keys($this->inmuebleValidationRules()) as $field) {
            $value                = $this->request->getPost($field);
            $submittedData[$field] = is_scalar($value) ? (string) $value : '';
        }

        return $submittedData;
    }

    private function validateInmuebleData(array $submittedData): bool
    {
        return $this->validateData($submittedData, $this->inmuebleValidationRules(), [
            'tipo' => [
                'required' => 'Ingresá el tipo de inmueble.',
                'in_list'  => 'Elegí un tipo válido: casa, departamento, local comercial, oficina o galpón.',
            ],
        ]);
    }

    private function normalizedInmuebleData(array $formData): array
    {
        $formData['tipo']  = $this->normalizeTipoInmueble($formData['tipo']);
        $formData['estado'] = $this->normalizeEstadoInmueble($formData['estado']);

        return $formData;
    }

    private function normalizeTipoInmueble(?string $tipo): string
    {
        $tipoNormalizado = trim((string) $tipo);

        return match (mb_strtolower($tipoNormalizado)) {
            'casa' => 'Casa',
            'departamento' => 'Departamento',
            'local comercial', 'local_comercial' => 'Local comercial',
            'oficina' => 'Oficina',
            'galpon', 'galpón' => 'Galpón',
            default => $tipoNormalizado,
        };
    }

    private function normalizeEstadoInmueble(?string $estado): string
    {
        $estadoNormalizado = trim((string) $estado);

        return match (mb_strtolower($estadoNormalizado)) {
            'activo' => 'Activo',
            'inactivo' => 'Inactivo',
            'ocupado' => 'Ocupado',
            'disponible' => 'Disponible',
            'en mantenimiento', 'en_mantenimiento', 'mantenimiento' => 'En Mantenimiento',
            default => $estadoNormalizado,
        };
    }

    private function isEstadoActivo(?string $estado): bool
    {
        return in_array(mb_strtolower(trim((string) $estado)), ['activo', 'ocupado'], true);
    }

    public function index(): string
    {
        $inmuebles = db_connect()
            ->table('inmuebles i')
            ->select([
                'i.id',
                'i.alias_nombre',
                'i.tipo',
                'i.superficie',
                'i.estado',
                'i.created_at AS inmueble_created_at',
                'i.updated_at AS inmueble_updated_at',
                'i.direccion_id',
                'd.calle',
                'd.numero',
                'd.piso_dpto',
                'd.ciudad',
                'd.created_at AS direccion_created_at',
                'd.updated_at AS direccion_updated_at',
            ])
            ->select('COUNT(isv.id) AS servicios_vinculados', false)
            ->select("COALESCE(SUM(CASE WHEN LOWER(TRIM(isv.estado)) IN ('activo', 'ocupado') THEN 1 ELSE 0 END), 0) AS servicios_activos", false)
            ->select("GROUP_CONCAT(DISTINCT CASE WHEN LOWER(TRIM(isv.estado)) IN ('activo', 'ocupado') THEN s.nombre END ORDER BY s.nombre SEPARATOR '|') AS nombres_servicios_activos", false)
            ->join('direcciones d', 'd.id = i.direccion_id')
            ->join('inmueble_servicios isv', 'isv.inmueble_id = i.id', 'left')
            ->join('servicios s', 's.id = isv.servicio_id', 'left')
            ->groupBy([
                'i.id',
                'i.alias_nombre',
                'i.tipo',
                'i.superficie',
                'i.estado',
                'i.created_at',
                'i.updated_at',
                'i.direccion_id',
                'd.calle',
                'd.numero',
                'd.piso_dpto',
                'd.ciudad',
                'd.created_at',
                'd.updated_at',
            ])
            ->orderBy('COALESCE(i.updated_at, i.created_at)', 'DESC', false)
            ->orderBy('i.id', 'DESC')
            ->get()
            ->getResultArray();

        $serviciosPorInmueble = [];
        $asociaciones = db_connect()
            ->table('inmueble_servicios isv')
            ->select([
                'isv.id AS asociacion_id',
                'isv.inmueble_id',
                'isv.servicio_id',
                'isv.titular_inmueble',
                'isv.estado AS estado_asociacion',
                'isv.nro_medidor',
                'isv.nro_cuenta_contrato',
                'isv.cod_pago_electronico',
                'isv.domicilio_facturacion',
                'isv.created_at AS asociacion_created_at',
                'isv.updated_at AS asociacion_updated_at',
                's.nombre AS servicio_nombre',
                's.categoria AS servicio_categoria',
                's.unidad_medida_std',
                's.proveedor',
            ])
            ->join('servicios s', 's.id = isv.servicio_id')
            ->orderBy('s.nombre', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($asociaciones as $asociacion) {
            $serviciosPorInmueble[(int) $asociacion['inmueble_id']][] = $asociacion;
        }

        $resumen = [
            'total'             => count($inmuebles),
            'activos'           => count(array_filter($inmuebles, fn (array $inmueble): bool => $this->isEstadoActivo($inmueble['estado']))),
            'superficie_total'  => array_sum(array_column($inmuebles, 'superficie')),
            'servicios_activos' => array_sum(array_column($inmuebles, 'servicios_activos')),
        ];

        return view('inmuebles/index', [
            'inmuebles'           => $inmuebles,
            'serviciosPorInmueble' => $serviciosPorInmueble,
            'resumen'             => $resumen,
        ]);
    }

    public function create(): RedirectResponse
    {
        $submittedData = $this->submittedInmuebleData();

        if (! $this->validateInmuebleData($submittedData)) {
            return redirect()->to(site_url('inmuebles'))
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('formData', $submittedData);
        }

        $formData = $this->normalizedInmuebleData($this->validator->getValidated());

        $db = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->transStart();
        $db->table('direcciones')->insert([
            'calle'     => trim($formData['calle']),
            'numero'    => trim($formData['numero']),
            'piso_dpto' => trim($formData['piso_dpto'] ?? '') ?: null,
            'ciudad'    => trim($formData['ciudad']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $direccionId = $db->insertID();

        $db->table('inmuebles')->insert([
            'alias_nombre' => trim($formData['alias_nombre']),
            'tipo'         => trim($formData['tipo']),
            'superficie'   => $formData['superficie'],
            'estado'       => $formData['estado'],
            'direccion_id' => $direccionId,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);
        $db->transComplete();

        if (! $db->transStatus()) {
            log_message('error', 'No se pudo guardar el inmueble y su dirección.');

            return redirect()->to(site_url('inmuebles'))
                ->withInput()
                ->with('formData', $submittedData)
                ->with('errors', ['database' => 'No se pudo guardar el inmueble. Revisá la información e intentá nuevamente.']);
        }

        return redirect()->to(site_url('inmuebles'))
            ->with('success', 'El inmueble se registró correctamente.');
    }

    public function update(int $id): RedirectResponse
    {
        $db = db_connect();
        $inmueble = $db->table('inmuebles')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($inmueble === null) {
            return redirect()->to(site_url('inmuebles'))
                ->with('error', 'No se encontró el inmueble que intentás modificar.');
        }

        $submittedData = $this->submittedInmuebleData();

        if (! $this->validateInmuebleData($submittedData)) {
            return redirect()->to(site_url('inmuebles'))
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('formData', $submittedData)
                ->with('editId', $id);
        }

        $formData = $this->normalizedInmuebleData($this->validator->getValidated());

        $now = date('Y-m-d H:i:s');
        $db->transStart();
        $db->table('direcciones')
            ->where('id', $inmueble['direccion_id'])
            ->update([
                'calle'     => trim($formData['calle']),
                'numero'    => trim($formData['numero']),
                'piso_dpto' => trim($formData['piso_dpto'] ?? '') ?: null,
                'ciudad'    => trim($formData['ciudad']),
                'updated_at' => $now,
            ]);
        $db->table('inmuebles')
            ->where('id', $id)
            ->update([
                'alias_nombre' => trim($formData['alias_nombre']),
                'tipo'         => trim($formData['tipo']),
                'superficie'   => $formData['superficie'],
                'estado'       => $formData['estado'],
                'updated_at'   => $now,
            ]);
        $db->transComplete();

        if (! $db->transStatus()) {
            log_message('error', 'No se pudo actualizar el inmueble {id} y su dirección.', ['id' => $id]);

            return redirect()->to(site_url('inmuebles'))
                ->withInput()
                ->with('formData', $submittedData)
                ->with('editId', $id)
                ->with('errors', ['database' => 'No se pudo actualizar el inmueble. Revisá la información e intentá nuevamente.']);
        }

        return redirect()->to(site_url('inmuebles'))
            ->with('success', 'El inmueble se actualizó correctamente.');
    }

    public function deactivate(int $id): RedirectResponse
    {
        $db = db_connect();
        $inmueble = $db->table('inmuebles')
            ->select('id')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($inmueble === null) {
            return redirect()->to(site_url('inmuebles'))
                ->with('error', 'No se encontró el inmueble que intentás dar de baja.');
        }

        $db->transStart();
        $db->table('inmuebles')
            ->where('id', $id)
            ->update([
                'estado' => 'Inactivo',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        $db->transComplete();

        if (! $db->transStatus()) {
            log_message('error', 'No se pudo dar de baja el inmueble {id}.', ['id' => $id]);

            return redirect()->to(site_url('inmuebles'))
                ->with('error', 'No se pudo dar de baja el inmueble. Intentá nuevamente.');
        }

        return redirect()->to(site_url('inmuebles'))
            ->with('success', 'El inmueble se dio de baja y quedó marcado como Inactivo.');
    }
}

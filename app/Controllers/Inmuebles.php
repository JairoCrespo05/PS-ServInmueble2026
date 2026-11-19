<?php

namespace App\Controllers;

use App\Models\FacturaModel;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class Inmuebles extends BaseController
{
    private const GEOREF_BASE_URL = 'https://apis.datos.gob.ar/georef/api/';

    private function currentUserId(): int
    {
        $userId = session()->get('user_id');

        return is_numeric($userId) ? (int) $userId : 0;
    }

    private function userOwnsInmueble(int $inmuebleId): bool
    {
        if ($this->currentUserId() <= 0) {
            return false;
        }

        return db_connect()
            ->table('inmuebles')
            ->where('id', $inmuebleId)
            ->where('usuario_id', $this->currentUserId())
            ->countAllResults() > 0;
    }

    private function inmuebleValidationRules(): array
    {
        return [
            'alias_nombre' => 'required|max_length[150]',
            'tipo'         => 'required|max_length[100]|in_list[Casa,Departamento,Local comercial,Oficina,Galpón,local comercial,casa,departamento,oficina,galpón]',
            'superficie'   => 'required|decimal|greater_than_equal_to[50]|less_than_equal_to[99999999.99]',
            'estado'       => 'required|in_list[Ocupado,Disponible,En Mantenimiento,ocupado,disponible,en mantenimiento]',
            'calle'        => 'required|max_length[150]',
            'numero'       => 'required|max_length[20]',
            'piso_dpto'    => 'permit_empty|max_length[50]',
            'provincia'    => 'required|max_length[100]',
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
        if (! $this->validateData($submittedData, $this->inmuebleValidationRules(), [
            'tipo' => [
                'required' => 'Ingresá el tipo de inmueble.',
                'in_list'  => 'Elegí un tipo válido: casa, departamento, local comercial, oficina o galpón.',
            ],
            'superficie' => [
                'greater_than_equal_to' => 'La superficie debe ser de al menos 50 m².',
            ],
            'estado' => [
                'in_list' => 'Elegí Disponible, Ocupado o En Mantenimiento.',
            ],
            'provincia' => [
                'required' => 'Seleccioná una provincia.',
            ],
            'ciudad' => [
                'required' => 'Seleccioná una ciudad o localidad.',
            ],
        ])) {
            return false;
        }

        return $this->validateProvinceAndLocality($submittedData);
    }

    private function georefRequest(string $resource, array $query): ?array
    {
        $cacheKey = 'georef_' . hash('sha256', $resource . serialize($query));
        $cached   = cache()->get($cacheKey);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = \Config\Services::curlrequest([
                'timeout'         => 8,
                'connect_timeout' => 3,
            ], null, null, false)->get(self::GEOREF_BASE_URL . $resource, [
                'query' => $query,
            ]);
        } catch (HTTPException $exception) {
            log_message('error', 'No se pudo consultar GeoRef: {message}', ['message' => $exception->getMessage()]);

            return null;
        }

        if ($response->getStatusCode() !== 200) {
            log_message('error', 'GeoRef respondió con estado HTTP {status}.', ['status' => $response->getStatusCode()]);

            return null;
        }

        $data = json_decode($response->getBody(), true);
        if (! is_array($data)) {
            log_message('error', 'GeoRef respondió con un JSON inválido para el recurso {resource}.', ['resource' => $resource]);

            return null;
        }

        cache()->save($cacheKey, $data, 86400);

        return $data;
    }

    private function georefProvinces(): ?array
    {
        $data = $this->georefRequest('provincias', [
            'campos' => 'id,nombre',
            'orden'  => 'nombre',
            'max'    => 100,
        ]);
        $provinces = $data['provincias'] ?? null;

        if (! is_array($provinces)) {
            if ($data !== null) {
                log_message('error', 'GeoRef respondió sin el listado de provincias esperado.');
            }

            return null;
        }

        return $provinces;
    }

    private function georefLocalities(string $provinceId): ?array
    {
        $data = $this->georefRequest('localidades', [
            'campos'    => 'id,nombre',
            'orden'     => 'nombre',
            'provincia' => $provinceId,
            'max'       => 5000,
        ]);
        $localities = $data['localidades'] ?? null;

        if (! is_array($localities)) {
            if ($data !== null) {
                log_message('error', 'GeoRef respondió sin el listado de localidades esperado.');
            }

            return null;
        }

        return $localities;
    }

    private function validateProvinceAndLocality(array $submittedData): bool
    {
        $provinces = $this->georefProvinces();

        if ($provinces === null) {
            $this->validator->setError('provincia', 'No se pudo consultar GeoRef para validar la provincia. Intentá nuevamente.');

            return false;
        }

        $provinceId = null;
        foreach ($provinces as $province) {
            if (($province['nombre'] ?? null) === trim($submittedData['provincia'])) {
                $provinceId = $province['id'] ?? null;
                break;
            }
        }

        if (! is_string($provinceId)) {
            $this->validator->setError('provincia', 'Seleccioná una provincia válida del listado.');

            return false;
        }

        $localities = $this->georefLocalities($provinceId);
        if ($localities === null) {
            $this->validator->setError('ciudad', 'No se pudo consultar GeoRef para validar la localidad. Intentá nuevamente.');

            return false;
        }

        foreach ($localities as $locality) {
            if (($locality['nombre'] ?? null) === trim($submittedData['ciudad'])) {
                return true;
            }
        }

        $this->validator->setError('ciudad', 'Seleccioná una localidad perteneciente a la provincia elegida.');

        return false;
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

    private function isEstadoDisponible(?string $estado): bool
    {
        return mb_strtolower(trim((string) $estado)) === 'disponible';
    }

    public function index(): string
    {
        $usuarioId = $this->currentUserId();

        $inmuebles = db_connect()
            ->table('inmuebles i')
            ->where('i.usuario_id', $usuarioId)
            ->select([
                'i.id',
                'i.alias_nombre',
                'i.tipo',
                'i.superficie',
                'i.estado',
                'i.estado_actividad',
                'i.created_at AS inmueble_created_at',
                'i.updated_at AS inmueble_updated_at',
                'i.direccion_id',
                'd.calle',
                'd.numero',
                'd.piso_dpto',
                'd.provincia',
                'd.ciudad',
                'd.created_at AS direccion_created_at',
                'd.updated_at AS direccion_updated_at',
            ])
            ->join('direcciones d', 'd.id = i.direccion_id')
            ->orderBy('COALESCE(i.updated_at, i.created_at)', 'DESC', false)
            ->orderBy('i.id', 'DESC')
            ->get()
            ->getResultArray();

        $serviciosPorInmueble = [];
        $serviciosActivosPorInmueble = [];
        $nombresServiciosActivosPorInmueble = [];
        $asociaciones = db_connect()
            ->table('inmueble_servicios isv')
            ->join('inmuebles i', 'i.id = isv.inmueble_id', 'left')
            ->where('i.usuario_id', $usuarioId)
            ->select([
                'isv.id AS asociacion_id',
                'isv.inmueble_id',
                'isv.servicio_id',
                'isv.titular_inmueble',
                'isv.estado AS estado_asociacion',
                'isv.estado_vinculacion',
                'isv.nro_medidor',
                'isv.nro_cuenta_contrato',
                'isv.cod_pago_electronico',
                'isv.domicilio_facturacion',
                'isv.created_at AS asociacion_created_at',
                'isv.updated_at AS asociacion_updated_at',
                'i.estado_actividad AS inmueble_estado_actividad',
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
            $inmuebleId = (int) $asociacion['inmueble_id'];
            $serviciosPorInmueble[$inmuebleId][] = $asociacion;

            if (
                $asociacion['inmueble_estado_actividad'] === 'Activo'
                && mb_strtolower(trim($asociacion['estado_asociacion'])) !== 'inactivo'
                && $asociacion['estado_vinculacion'] === 'Activo'
            ) {
                $nombre = (string) $asociacion['servicio_nombre'];
                $serviciosActivosPorInmueble[$inmuebleId] = ($serviciosActivosPorInmueble[$inmuebleId] ?? 0) + 1;
                $nombresServiciosActivosPorInmueble[$inmuebleId][mb_strtolower($nombre)] = $nombre;
            }
        }

        foreach ($inmuebles as &$inmueble) {
            $nombres = array_values($nombresServiciosActivosPorInmueble[(int) $inmueble['id']] ?? []);
            $inmueble['servicios_activos'] = $serviciosActivosPorInmueble[(int) $inmueble['id']] ?? 0;
            $inmueble['nombres_servicios_activos'] = implode('|', $nombres);
        }
        unset($inmueble);

        $resumen = [
            'total'             => count($inmuebles),
            'disponibles'       => count(array_filter($inmuebles, fn (array $inmueble): bool => $inmueble['estado_actividad'] === 'Activo' && $this->isEstadoDisponible($inmueble['estado']))),
            'superficie_total'  => array_sum(array_column($inmuebles, 'superficie')),
            'servicios_activos' => array_sum(array_column($inmuebles, 'servicios_activos')),
        ];

        return view('inmuebles/index', [
            'inmuebles'           => $inmuebles,
            'serviciosPorInmueble' => $serviciosPorInmueble,
            'resumen'             => $resumen,
            'facturasPendientesPorInmueble' => (new FacturaModel())->countPendingByPropertyForOwner($usuarioId),
        ]);
    }

    public function geoProvinces(): ResponseInterface
    {
        $provinces = $this->georefProvinces();

        if ($provinces === null) {
            return $this->response->setStatusCode(502)->setJSON([
                'error' => 'No se pudo cargar el catálogo de provincias. Revisá la conexión e intentá nuevamente.',
            ]);
        }

        return $this->response->setJSON([
            'provincias' => array_map(
                static fn (array $province): array => [
                    'id'     => (string) ($province['id'] ?? ''),
                    'nombre' => (string) ($province['nombre'] ?? ''),
                ],
                $provinces
            ),
        ]);
    }

    public function geoLocalities(string $provinceId): ResponseInterface
    {
        if (! preg_match('/^\d{2}$/', $provinceId)) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'La provincia seleccionada no es válida.',
            ]);
        }

        $provinces = $this->georefProvinces();
        if ($provinces === null) {
            return $this->response->setStatusCode(502)->setJSON([
                'error' => 'No se pudo validar la provincia. Revisá la conexión e intentá nuevamente.',
            ]);
        }

        $provinceExists = false;
        foreach ($provinces as $province) {
            if (($province['id'] ?? null) === $provinceId) {
                $provinceExists = true;
                break;
            }
        }

        if (! $provinceExists) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => 'La provincia seleccionada no pertenece a Argentina.',
            ]);
        }

        $localities = $this->georefLocalities($provinceId);
        if ($localities === null) {
            return $this->response->setStatusCode(502)->setJSON([
                'error' => 'No se pudo cargar el catálogo de localidades. Revisá la conexión e intentá nuevamente.',
            ]);
        }

        return $this->response->setJSON([
            'localidades' => array_map(
                static fn (array $locality): string => (string) ($locality['nombre'] ?? ''),
                $localities
            ),
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
        $usuarioId = $this->currentUserId();

        $db->transStart();
        $db->table('direcciones')->insert([
            'calle'      => trim($formData['calle']),
            'numero'     => trim($formData['numero']),
            'piso_dpto'  => trim($formData['piso_dpto'] ?? '') ?: null,
            'provincia'  => trim($formData['provincia']),
            'ciudad'     => trim($formData['ciudad']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $direccionId = $db->insertID();

        $db->table('inmuebles')->insert([
            'alias_nombre' => trim($formData['alias_nombre']),
            'tipo'         => trim($formData['tipo']),
            'superficie'   => $formData['superficie'],
            'estado'       => $formData['estado'],
            'estado_actividad' => 'Activo',
            'usuario_id'   => $usuarioId,
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
            ->where('usuario_id', $this->currentUserId())
            ->get()
            ->getRowArray();

        if ($inmueble === null) {
            return redirect()->to(site_url('inmuebles'))
                ->with('error', 'No se encontró el inmueble que intentás modificar o no tenés permisos sobre él.');
        }

        $submittedData = $this->submittedInmuebleData();

        $reactivating = $inmueble['estado_actividad'] === 'Inactivo';
        $submittedData['reactivar_inmueble'] = $this->request->getPost('reactivar_inmueble') === '1' ? '1' : '';
        $valid = $this->validateInmuebleData($submittedData);
        if ($reactivating && $submittedData['reactivar_inmueble'] !== '1') {
            $this->validator->setError('reactivar_inmueble', 'Confirmá que querés reactivar el inmueble.');
            $valid = false;
        }

        if (! $valid) {
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
                'calle'      => trim($formData['calle']),
                'numero'     => trim($formData['numero']),
                'piso_dpto'  => trim($formData['piso_dpto'] ?? '') ?: null,
                'provincia'  => trim($formData['provincia']),
                'ciudad'     => trim($formData['ciudad']),
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
            ] + ($reactivating ? ['estado_actividad' => 'Activo'] : []));
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
            ->where('usuario_id', $this->currentUserId())
            ->get()
            ->getRowArray();

        if ($inmueble === null) {
            return redirect()->to(site_url('inmuebles'))
                ->with('error', 'No se encontró el inmueble que intentás dar de baja o no tenés permisos sobre él.');
        }

        $db->transStart();
        $db->table('inmuebles')
            ->where('id', $id)
            ->update([
                'estado_actividad' => 'Inactivo',
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

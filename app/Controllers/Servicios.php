<?php

namespace App\Controllers;

use App\Models\ServicioModel;
use App\Models\InmuebleServicioModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

class Servicios extends BaseController
{
    private const UNIDADES_POR_CATEGORIA = [
        'Agua'        => 'm3',
        'Electricidad' => 'kWh',
        'Gas'         => 'm3',
        'Internet'    => 'Mes',
        'Telefonía'   => 'Mes',
        'Televisión'  => 'Mes',
        'Expensas'    => 'Mes',
    ];

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

    private function userOwnsAssociation(int $asociacionId): bool
    {
        if ($this->currentUserId() <= 0) {
            return false;
        }

        return db_connect()
            ->table('inmueble_servicios isv')
            ->join('inmuebles i', 'i.id = isv.inmueble_id', 'inner')
            ->where('isv.id', $asociacionId)
            ->where('i.usuario_id', $this->currentUserId())
            ->countAllResults() > 0;
    }

    private function submittedTipoServicioData(): array
    {
        $submittedData = [];

        foreach (['nombre', 'unidad_medida_std', 'proveedor', 'categoria'] as $field) {
            $value = $this->request->getPost($field);
            $submittedData[$field] = is_scalar($value) ? trim((string) $value) : '';
        }

        return $submittedData;
    }

    private function validateTipoServicioData(array $submittedData, bool $esAlta = false): bool
    {
        return $this->validateData($submittedData, [
            'nombre'            => 'required|max_length[100]',
            'unidad_medida_std' => $esAlta ? 'max_length[30]' : 'required|max_length[30]',
            'proveedor'         => 'required|max_length[150]',
            'categoria'         => $esAlta
                ? 'required|in_list[' . implode(',', array_keys(self::UNIDADES_POR_CATEGORIA)) . ']'
                : 'required|max_length[100]',
        ], [
            'nombre' => [
                'required' => 'Ingresá el nombre del servicio.',
            ],
            'unidad_medida_std' => [
                'required' => 'Ingresá la unidad de medida.',
            ],
            'proveedor' => [
                'required' => 'Ingresá el proveedor.',
            ],
            'categoria' => [
                'required' => 'Ingresá la categoría.',
                'in_list'  => 'Seleccioná una categoría válida.',
            ],
        ]);
    }

    public function index(): ResponseInterface
    {
        $servicios = (new ServicioModel())
            ->orderBy('COALESCE(updated_at, created_at)', 'DESC', false)
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->response->setJSON($servicios);
    }

    public function gestionarTipos(): string
    {
        $servicios = (new ServicioModel())
            ->orderBy('COALESCE(updated_at, created_at)', 'DESC', false)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('servicios/tipos', [
            'servicios' => $servicios,
            'unidadesPorCategoria' => self::UNIDADES_POR_CATEGORIA,
            'formData'  => session()->getFlashdata('tipoServicioFormData') ?? [],
            'errors'    => session()->getFlashdata('tipoServicioErrors') ?? [],
            'editId'    => session()->getFlashdata('tipoServicioEditId'),
            'success'   => session()->getFlashdata('tipoServicioSuccess'),
            'error'     => session()->getFlashdata('tipoServicioError'),
        ]);
    }

    public function crearTipo(): RedirectResponse
    {
        $submittedData = $this->submittedTipoServicioData();
        $submittedData['unidad_medida_std'] = self::UNIDADES_POR_CATEGORIA[$submittedData['categoria']] ?? '';

        if (! $this->validateTipoServicioData($submittedData, true)) {
            return redirect()->to(site_url('tipos-servicio'))
                ->withInput()
                ->with('tipoServicioErrors', $this->validator->getErrors())
                ->with('tipoServicioFormData', $submittedData);
        }

        $db = db_connect();
        $modelo = new ServicioModel();
        $db->transStart();
        $insertado = $modelo->insert($submittedData);
        $db->transComplete();

        if (! $insertado || ! $db->transStatus()) {
            log_message('error', 'No se pudo crear el tipo de servicio.');

            return redirect()->to(site_url('tipos-servicio'))
                ->with('tipoServicioFormData', $submittedData)
                ->with('tipoServicioError', 'No se pudo guardar el servicio. Revisá los datos e intentá nuevamente.');
        }

        return redirect()->to(site_url('tipos-servicio'))
            ->with('tipoServicioSuccess', 'El servicio se registró correctamente.');
    }

    public function actualizarTipo(int $id): RedirectResponse
    {
        $modelo = new ServicioModel();
        if ($modelo->find($id) === null) {
            return redirect()->to(site_url('tipos-servicio'))
                ->with('tipoServicioError', 'No se encontró el servicio que intentás modificar.');
        }

        $submittedData = $this->submittedTipoServicioData();

        if (! $this->validateTipoServicioData($submittedData)) {
            return redirect()->to(site_url('tipos-servicio'))
                ->withInput()
                ->with('tipoServicioErrors', $this->validator->getErrors())
                ->with('tipoServicioFormData', $submittedData)
                ->with('tipoServicioEditId', $id);
        }

        $db = db_connect();
        $db->transStart();
        $actualizado = $modelo->update($id, $submittedData);
        $db->transComplete();

        if (! $actualizado || ! $db->transStatus()) {
            log_message('error', 'No se pudo actualizar el tipo de servicio {id}.', ['id' => $id]);

            return redirect()->to(site_url('tipos-servicio'))
                ->with('tipoServicioFormData', $submittedData)
                ->with('tipoServicioEditId', $id)
                ->with('tipoServicioError', 'No se pudo actualizar el servicio. Revisá los datos e intentá nuevamente.');
        }

        return redirect()->to(site_url('tipos-servicio'))
            ->with('tipoServicioSuccess', 'Los datos del servicio se actualizaron correctamente.');
    }

    public function darDeBajaTipo(int $id): RedirectResponse
    {
        $modelo = new ServicioModel();
        if ($modelo->find($id) === null) {
            return redirect()->to(site_url('tipos-servicio'))
                ->with('tipoServicioError', 'No se encontró el servicio que intentás eliminar.');
        }

        $db = db_connect();
        $asociaciones = $db->table('inmueble_servicios')
            ->where('servicio_id', $id)
            ->countAllResults();

        if ($asociaciones > 0) {
            return redirect()->to(site_url('tipos-servicio'))
                ->with('tipoServicioError', 'No se puede eliminar este servicio porque tiene asociaciones registradas. El historial se conserva.');
        }

        $db->transStart();
        $eliminado = $modelo->delete($id);
        $db->transComplete();

        if (! $eliminado || ! $db->transStatus()) {
            log_message('error', 'No se pudo eliminar el tipo de servicio {id}.', ['id' => $id]);

            return redirect()->to(site_url('tipos-servicio'))
                ->with('tipoServicioError', 'No se pudo eliminar el servicio. Intentá nuevamente.');
        }

        return redirect()->to(site_url('tipos-servicio'))
            ->with('tipoServicioSuccess', 'El servicio se eliminó correctamente.');
    }

    
public function gestionar(): string
{
    $db = db_connect();
    $usuarioId = $this->currentUserId();

    // 1. Obtener el catálogo completo de servicios.
    $filasServicios = $db
        ->table('servicios')
        ->select([
            'id',
            'nombre',
            'unidad_medida_std',
            'proveedor',
            'categoria',
        ])
        ->orderBy('nombre', 'ASC')
        ->get()
        ->getResultArray();

    // 2. Preparar el catálogo indexado por ID.
    $servicios = [];

    foreach ($filasServicios as $fila) {
        $servicioId = (int) $fila['id'];

        $servicios[$servicioId] = [
            'id'                => $servicioId,
            'nombre'            => $fila['nombre'],
            'unidad_medida_std' => $fila['unidad_medida_std'],
            'proveedor'         => $fila['proveedor'],
            'categoria'         => $fila['categoria'],
            'inmuebles'         => [],
            'inmueble_ids'      => [],
        ];
    }

    // 3. Obtener solamente las asociaciones de inmuebles propios.
    $filasAsociaciones = $db
        ->table('inmueble_servicios isv')
        ->join('inmuebles i', 'i.id = isv.inmueble_id')
        ->join('direcciones d', 'd.id = i.direccion_id')
        ->where('i.usuario_id', $usuarioId)
        ->select([
            'isv.id AS asociacion_id',
            'isv.servicio_id',
            'isv.estado AS estado_asociacion',
            "CASE WHEN i.estado_actividad = 'Inactivo' OR LOWER(TRIM(isv.estado)) = 'inactivo' THEN 'Inactivo' ELSE isv.estado_vinculacion END AS estado_servicio",
            'isv.titular_inmueble',
            'isv.nro_medidor',
            'isv.nro_cuenta_contrato',
            'isv.cod_pago_electronico',
            'isv.domicilio_facturacion',
            'i.id AS inmueble_id',
            'i.alias_nombre',
            'i.tipo',
            'i.estado AS estado_inmueble',
            'i.estado_actividad',
            'd.calle',
            'd.numero',
            'd.piso_dpto',
            'd.ciudad',
        ])
        ->orderBy('COALESCE(isv.updated_at, isv.created_at)', 'DESC', false)
        ->orderBy('isv.id', 'DESC')
        ->get()
        ->getResultArray();

    // 4. Incorporar las asociaciones a cada servicio.
    $asociaciones = 0;
    $inmueblesConServicios = [];

    foreach ($filasAsociaciones as $fila) {
        $servicioId = (int) $fila['servicio_id'];

        // Protección ante asociaciones cuyo servicio no esté en el catálogo.
        if (! isset($servicios[$servicioId])) {
            continue;
        }

        $asociaciones++;

        $inmuebleId = (int) $fila['inmueble_id'];
        $inmueblesConServicios[$inmuebleId] = true;

        $servicios[$servicioId]['inmueble_ids'][] = $inmuebleId;

        $servicios[$servicioId]['inmuebles'][] = [
            'id'                    => $inmuebleId,
            'alias_nombre'          => $fila['alias_nombre'],
            'tipo'                  => $fila['tipo'],
            'estado_inmueble'       => $fila['estado_inmueble'],
            'estado_actividad'      => $fila['estado_actividad'],
            'estado_servicio'       => $fila['estado_servicio'],
            'nro_medidor'           => $fila['nro_medidor'],
            'titular_inmueble'      => $fila['titular_inmueble'],
            'nro_cuenta_contrato'   => $fila['nro_cuenta_contrato'],
            'cod_pago_electronico'  => $fila['cod_pago_electronico'],
            'domicilio_facturacion' => $fila['domicilio_facturacion'],
            'asociacion_id'         => (int) $fila['asociacion_id'],
            'calle'                 => $fila['calle'],
            'numero'                => $fila['numero'],
            'piso_dpto'             => $fila['piso_dpto'],
            'ciudad'                => $fila['ciudad'],
        ];
    }

    // 5. Mantener el catálogo para el formulario, pero listar solo servicios con asociaciones.
    $catalogoServicios = array_values($servicios);
    $servicios = array_values(array_filter(
        $catalogoServicios,
        static fn (array $servicio): bool => $servicio['inmuebles'] !== [],
    ));

    // 6. Obtener los inmuebles del propietario autenticado.
    $inmuebles = $db
        ->table('inmuebles i')
        ->select([
            'i.id',
            'i.alias_nombre',
            'i.tipo',
            'i.estado',
            'i.estado_actividad',
            'd.calle',
            'd.numero',
            'd.piso_dpto',
            'd.ciudad',
        ])
        ->join('direcciones d', 'd.id = i.direccion_id')
        ->where('i.usuario_id', $usuarioId)
        ->orderBy('i.alias_nombre', 'ASC')
        ->get()
        ->getResultArray();

    // 7. Renderizar la vista.
    return view('servicios/index', [
        'servicios'         => $servicios,
        'catalogoServicios' => $catalogoServicios,
        'inmuebles'         => $inmuebles,
        'formData'          => session()->getFlashdata('formData') ?? [],
        'errors'            => session()->getFlashdata('errors') ?? [],
        'serviceLocked'     => session()->getFlashdata('serviceLocked') ?? false,
        'editAssociationId' => session()->getFlashdata('editAssociationId'),
        'success'           => session()->getFlashdata('success'),
        'error'             => session()->getFlashdata('error'),
        'resumen'           => [
            'total'         => count($servicios),
            'asociaciones'  => $asociaciones,
            'inmuebles'     => count($inmueblesConServicios),
        ],
    ]);
}


    public function asociar(): RedirectResponse
    {
        $fields = [
            'servicio_id',
            'inmueble_id',
            'titular_inmueble',
            'estado_vinculacion',
            'nro_medidor',
            'nro_cuenta_contrato',
            'cod_pago_electronico',
            'domicilio_facturacion',
        ];
        $submittedData = [];

        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            $submittedData[$field] = is_scalar($value) ? trim((string) $value) : '';
        }
        $serviceLocked = $this->request->getPost('service_locked') === '1';

        $rules = [
            'servicio_id'           => 'required|is_natural_no_zero',
            'inmueble_id'           => 'required|is_natural_no_zero',
            'titular_inmueble'      => 'required|max_length[150]',
            'estado_vinculacion'    => 'required|in_list[' . implode(',', InmuebleServicioModel::ESTADOS_OPERATIVOS) . ']',
            'nro_medidor'           => 'permit_empty|max_length[50]',
            'nro_cuenta_contrato'   => 'permit_empty|max_length[100]',
            'cod_pago_electronico'  => 'permit_empty|max_length[100]',
            'domicilio_facturacion' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validateData($submittedData, $rules)) {
            return redirect()->to(site_url('servicios/asociaciones'))
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('formData', $submittedData)
                ->with('serviceLocked', $serviceLocked);
        }

        $db = db_connect();
        $servicioId = (int) $submittedData['servicio_id'];
        $inmuebleId = (int) $submittedData['inmueble_id'];
        $servicioExiste = $db->table('servicios')->where('id', $servicioId)->countAllResults() > 0;
        $inmueble = $db->table('inmuebles')->select('estado_actividad')->where('id', $inmuebleId)->where('usuario_id', $this->currentUserId())->get()->getRowArray();

        if (! $servicioExiste || $inmueble === null) {
            return redirect()->to(site_url('servicios/asociaciones'))
                ->withInput()
                ->with('formData', $submittedData)
                ->with('serviceLocked', $serviceLocked)
                ->with('error', 'El servicio o el inmueble seleccionado ya no existe o no te pertenece.');
        }

        if ($submittedData['estado_vinculacion'] === 'Activo' && $inmueble['estado_actividad'] !== 'Activo') {
            return redirect()->to(site_url('servicios/asociaciones'))
                ->withInput()
                ->with('formData', $submittedData)
                ->with('serviceLocked', $serviceLocked)
                ->with('errors', ['estado_vinculacion' => 'No podés activar un vínculo de un inmueble inactivo.']);
        }

        $asociacionExiste = $db->table('inmueble_servicios')
            ->where('servicio_id', $servicioId)
            ->where('inmueble_id', $inmuebleId)
            ->where("LOWER(TRIM(estado)) != 'inactivo'", null, false)
            ->countAllResults() > 0;

        if ($asociacionExiste) {
            return redirect()->to(site_url('servicios/asociaciones'))
                ->withInput()
                ->with('formData', $submittedData)
                ->with('serviceLocked', $serviceLocked)
                ->with('error', 'Ese servicio ya está asociado al inmueble seleccionado.');
        }

        $modelo = new InmuebleServicioModel();
        $db->transStart();
        $modelo->insert([
            'servicio_id'          => $servicioId,
            'inmueble_id'          => $inmuebleId,
            'titular_inmueble'     => $submittedData['titular_inmueble'],
            'estado'               => 'Activo',
            'estado_vinculacion'  => $submittedData['estado_vinculacion'],
            'nro_medidor'          => $submittedData['nro_medidor'] ?: null,
            'nro_cuenta_contrato'  => $submittedData['nro_cuenta_contrato'] ?: null,
            'cod_pago_electronico' => $submittedData['cod_pago_electronico'] ?: null,
            'domicilio_facturacion' => $submittedData['domicilio_facturacion'] ?: null,
        ]);
        $db->transComplete();

        if (! $db->transStatus()) {
            log_message('error', 'No se pudo asociar el servicio {servicio} al inmueble {inmueble}.', [
                'servicio' => $servicioId,
                'inmueble' => $inmuebleId,
            ]);

            return redirect()->to(site_url('servicios/asociaciones'))
                ->withInput()
                ->with('formData', $submittedData)
                ->with('serviceLocked', $serviceLocked)
                ->with('error', 'No se pudo guardar la asociación. Revisá los datos e intentá nuevamente.');
        }

        return redirect()->to(site_url('servicios/asociaciones'))
            ->with('success', 'El servicio se asoció correctamente al inmueble.');
    }

    public function actualizarAsociacion(int $id): RedirectResponse
    {
        $modelo = new InmuebleServicioModel();
        $asociacion = $modelo->find($id);
        if ($asociacion === null || ! $this->userOwnsAssociation($id) || mb_strtolower(trim($asociacion['estado'])) === 'inactivo') {
            return redirect()->to(site_url('servicios/asociaciones'))
                ->with('error', 'No se encontró la asociación que intentás modificar o no tenés permisos sobre ella.');
        }

        $fields = [
            'titular_inmueble',
            'estado_vinculacion',
            'nro_medidor',
            'nro_cuenta_contrato',
            'cod_pago_electronico',
            'domicilio_facturacion',
        ];
        $submittedData = [];

        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            $submittedData[$field] = is_scalar($value) ? trim((string) $value) : '';
        }
        $submittedData['servicio_id'] = (string) $asociacion['servicio_id'];
        $submittedData['inmueble_id'] = (string) $asociacion['inmueble_id'];

        $rules = [
            'titular_inmueble'      => 'required|max_length[150]',
            'estado_vinculacion'    => 'required|in_list[' . implode(',', InmuebleServicioModel::ESTADOS_OPERATIVOS) . ']',
            'nro_medidor'           => 'permit_empty|max_length[50]',
            'nro_cuenta_contrato'   => 'permit_empty|max_length[100]',
            'cod_pago_electronico'  => 'permit_empty|max_length[100]',
            'domicilio_facturacion' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validateData($submittedData, $rules)) {
            return redirect()->to(site_url('servicios/asociaciones'))
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('formData', $submittedData)
                ->with('editAssociationId', $id);
        }

        $inmueble = db_connect()->table('inmuebles')
            ->select('estado_actividad')
            ->where('id', $asociacion['inmueble_id'])
            ->where('usuario_id', $this->currentUserId())
            ->get()
            ->getRowArray();

        if ($inmueble === null) {
            return redirect()->to(site_url('servicios/asociaciones'))
                ->with('error', 'No se encontró el inmueble asociado o no tenés permisos sobre él.');
        }

        if ($inmueble['estado_actividad'] !== 'Activo') {
            return redirect()->to(site_url('servicios/asociaciones'))
                ->with('error', 'No podés modificar el vínculo mientras el inmueble esté dado de baja.');
        }

        $db = db_connect();
        $db->transStart();
        $modelo->update($id, [
            'titular_inmueble'      => $submittedData['titular_inmueble'],
            'estado_vinculacion'    => $submittedData['estado_vinculacion'],
            'nro_medidor'           => $submittedData['nro_medidor'] ?: null,
            'nro_cuenta_contrato'   => $submittedData['nro_cuenta_contrato'] ?: null,
            'cod_pago_electronico'  => $submittedData['cod_pago_electronico'] ?: null,
            'domicilio_facturacion' => $submittedData['domicilio_facturacion'] ?: null,
        ]);
        $db->transComplete();

        if (! $db->transStatus()) {
            log_message('error', 'No se pudo actualizar la asociación de servicio {id}.', ['id' => $id]);

            return redirect()->to(site_url('servicios/asociaciones'))
                ->withInput()
                ->with('formData', $submittedData)
                ->with('editAssociationId', $id)
                ->with('error', 'No se pudo actualizar la asociación. Revisá los datos e intentá nuevamente.');
        }

        return redirect()->to(site_url('servicios/asociaciones'))
            ->with('success', 'Los datos de la asociación se actualizaron correctamente.');
    }

    public function darDeBajaAsociacion(int $id): RedirectResponse
    {
        $modelo = new InmuebleServicioModel();
        if ($modelo->find($id) === null || ! $this->userOwnsAssociation($id)) {
            return redirect()->to(site_url('servicios/asociaciones'))
                ->with('error', 'No se encontró la asociación que intentás dar de baja o no tenés permisos sobre ella.');
        }

        $db = db_connect();
        $db->transStart();
        $modelo->update($id, ['estado' => 'Inactivo', 'motivo_suspension' => null]);
        $db->transComplete();

        if (! $db->transStatus()) {
            log_message('error', 'No se pudo dar de baja la asociación de servicio {id}.', ['id' => $id]);

            return redirect()->to(site_url('servicios/asociaciones'))
                ->with('error', 'No se pudo dar de baja la asociación. Intentá nuevamente.');
        }

        return redirect()->to(site_url('servicios/asociaciones'))
            ->with('success', 'La asociación se dio de baja y se conservó su historial.');
    }
}

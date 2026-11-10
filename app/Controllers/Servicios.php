<?php

namespace App\Controllers;

use App\Models\ServicioModel;
use App\Models\InmuebleServicioModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

class Servicios extends BaseController
{
    private function submittedTipoServicioData(): array
    {
        $submittedData = [];

        foreach (['nombre', 'unidad_medida_std', 'proveedor', 'categoria'] as $field) {
            $value = $this->request->getPost($field);
            $submittedData[$field] = is_scalar($value) ? trim((string) $value) : '';
        }

        return $submittedData;
    }

    private function validateTipoServicioData(array $submittedData): bool
    {
        return $this->validateData($submittedData, [
            'nombre'            => 'required|max_length[100]',
            'unidad_medida_std' => 'required|max_length[30]',
            'proveedor'         => 'required|max_length[150]',
            'categoria'         => 'required|max_length[100]',
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

        if (! $this->validateTipoServicioData($submittedData)) {
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
        $filas = $db
            ->table('servicios s')
            ->select([
                's.id AS servicio_id',
                's.nombre AS servicio_nombre',
                's.unidad_medida_std',
                's.proveedor',
                's.categoria',
                'isv.id AS asociacion_id',
                'isv.estado AS estado_servicio',
                'isv.titular_inmueble',
                'isv.nro_medidor',
                'isv.nro_cuenta_contrato',
                'isv.cod_pago_electronico',
                'isv.domicilio_facturacion',
                'i.id AS inmueble_id',
                'i.alias_nombre',
                'i.tipo',
                'i.estado AS estado_inmueble',
                'd.calle',
                'd.numero',
                'd.piso_dpto',
                'd.ciudad',
            ])
            ->join(
                'inmueble_servicios isv',
                "isv.servicio_id = s.id AND LOWER(TRIM(isv.estado)) != 'inactivo'",
                'left',
                false,
            )
            ->join('inmuebles i', 'i.id = isv.inmueble_id', 'left')
            ->join('direcciones d', 'd.id = i.direccion_id', 'left')
            ->orderBy('COALESCE(isv.updated_at, isv.created_at, s.updated_at, s.created_at)', 'DESC', false)
            ->orderBy('isv.id', 'DESC')
            ->orderBy('s.updated_at', 'DESC')
            ->orderBy('s.created_at', 'DESC')
            ->orderBy('s.id', 'DESC')
            ->get()
            ->getResultArray();

        $servicios = [];
        $asociaciones = 0;
        $inmueblesAsociados = [];

        foreach ($filas as $fila) {
            $servicioId = (int) $fila['servicio_id'];

            if (! isset($servicios[$servicioId])) {
                $servicios[$servicioId] = [
                    'id'                 => $servicioId,
                    'nombre'             => $fila['servicio_nombre'],
                    'unidad_medida_std'  => $fila['unidad_medida_std'],
                    'proveedor'          => $fila['proveedor'],
                    'categoria'          => $fila['categoria'],
                    'inmuebles'          => [],
                ];
            }

            if ($fila['asociacion_id'] === null) {
                continue;
            }

            $asociaciones++;
            $inmueblesAsociados[$servicioId][] = (int) $fila['inmueble_id'];
            $servicios[$servicioId]['inmuebles'][] = [
                'id'                    => (int) $fila['inmueble_id'],
                'alias_nombre'          => $fila['alias_nombre'],
                'tipo'                  => $fila['tipo'],
                'estado_inmueble'       => $fila['estado_inmueble'],
                'estado_servicio'       => $fila['estado_servicio'],
                'nro_medidor'           => $fila['nro_medidor'],
                'titular_inmueble'      => $fila['titular_inmueble'],
                'nro_cuenta_contrato' => $fila['nro_cuenta_contrato'],
                'cod_pago_electronico' => $fila['cod_pago_electronico'],
                'domicilio_facturacion' => $fila['domicilio_facturacion'],
                'asociacion_id'         => (int) $fila['asociacion_id'],
                'calle'                 => $fila['calle'],
                'numero'                => $fila['numero'],
                'piso_dpto'             => $fila['piso_dpto'],
                'ciudad'                => $fila['ciudad'],
            ];
        }

        foreach ($servicios as &$servicio) {
            $servicio['inmueble_ids'] = $inmueblesAsociados[$servicio['id']] ?? [];
        }
        unset($servicio);

        $servicios = array_values($servicios);
        $inmuebles = $db
            ->table('inmuebles i')
            ->select(['i.id', 'i.alias_nombre', 'i.tipo', 'i.estado', 'd.calle', 'd.numero', 'd.piso_dpto', 'd.ciudad'])
            ->join('direcciones d', 'd.id = i.direccion_id')
            ->orderBy('i.alias_nombre', 'ASC')
            ->get()
            ->getResultArray();

        return view('servicios/index', [
            'servicios'  => $servicios,
            'inmuebles'  => $inmuebles,
            'formData'   => session()->getFlashdata('formData') ?? [],
            'errors'     => session()->getFlashdata('errors') ?? [],
            'serviceLocked' => session()->getFlashdata('serviceLocked') ?? false,
            'editAssociationId' => session()->getFlashdata('editAssociationId'),
            'success'    => session()->getFlashdata('success'),
            'error'      => session()->getFlashdata('error'),
            'resumen'    => [
                'total'                 => count($servicios),
                'asociaciones'          => $asociaciones,
                'sin_inmuebles'         => count(array_filter(
                    $servicios,
                    static fn (array $servicio): bool => $servicio['inmuebles'] === [],
                )),
            ],
        ]);
    }

    public function asociar(): RedirectResponse
    {
        $fields = [
            'servicio_id',
            'inmueble_id',
            'titular_inmueble',
            'estado',
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
            'estado'                => 'required|in_list[Activo,Inactivo,Suspendido]',
            'nro_medidor'           => 'permit_empty|max_length[50]',
            'nro_cuenta_contrato'   => 'permit_empty|max_length[100]',
            'cod_pago_electronico'  => 'permit_empty|max_length[100]',
            'domicilio_facturacion' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validateData($submittedData, $rules)) {
            return redirect()->to(site_url('servicios'))
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('formData', $submittedData)
                ->with('serviceLocked', $serviceLocked);
        }

        $db = db_connect();
        $servicioId = (int) $submittedData['servicio_id'];
        $inmuebleId = (int) $submittedData['inmueble_id'];
        $servicioExiste = $db->table('servicios')->where('id', $servicioId)->countAllResults() > 0;
        $inmuebleExiste = $db->table('inmuebles')->where('id', $inmuebleId)->countAllResults() > 0;

        if (! $servicioExiste || ! $inmuebleExiste) {
            return redirect()->to(site_url('servicios'))
                ->withInput()
                ->with('formData', $submittedData)
                ->with('serviceLocked', $serviceLocked)
                ->with('error', 'El servicio o el inmueble seleccionado ya no existe.');
        }

        $asociacionExiste = $db->table('inmueble_servicios')
            ->where('servicio_id', $servicioId)
            ->where('inmueble_id', $inmuebleId)
            ->where("LOWER(TRIM(estado)) !=", 'inactivo', false)
            ->countAllResults() > 0;

        if ($asociacionExiste) {
            return redirect()->to(site_url('servicios'))
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
            'estado'               => $submittedData['estado'],
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

            return redirect()->to(site_url('servicios'))
                ->withInput()
                ->with('formData', $submittedData)
                ->with('serviceLocked', $serviceLocked)
                ->with('error', 'No se pudo guardar la asociación. Revisá los datos e intentá nuevamente.');
        }

        return redirect()->to(site_url('servicios'))
            ->with('success', 'El servicio se asoció correctamente al inmueble.');
    }

    public function actualizarAsociacion(int $id): RedirectResponse
    {
        $modelo = new InmuebleServicioModel();
        $asociacion = $modelo->find($id);
        if ($asociacion === null) {
            return redirect()->to(site_url('servicios'))
                ->with('error', 'No se encontró la asociación que intentás modificar.');
        }

        $fields = [
            'titular_inmueble',
            'estado',
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
            'estado'                => 'required|in_list[Activo,Inactivo,Suspendido]',
            'nro_medidor'           => 'permit_empty|max_length[50]',
            'nro_cuenta_contrato'   => 'permit_empty|max_length[100]',
            'cod_pago_electronico'  => 'permit_empty|max_length[100]',
            'domicilio_facturacion' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validateData($submittedData, $rules)) {
            return redirect()->to(site_url('servicios'))
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('formData', $submittedData)
                ->with('editAssociationId', $id);
        }

        $db = db_connect();
        $db->transStart();
        $modelo->update($id, [
            'titular_inmueble'      => $submittedData['titular_inmueble'],
            'estado'                => $submittedData['estado'],
            'nro_medidor'           => $submittedData['nro_medidor'] ?: null,
            'nro_cuenta_contrato'   => $submittedData['nro_cuenta_contrato'] ?: null,
            'cod_pago_electronico'  => $submittedData['cod_pago_electronico'] ?: null,
            'domicilio_facturacion' => $submittedData['domicilio_facturacion'] ?: null,
        ]);
        $db->transComplete();

        if (! $db->transStatus()) {
            log_message('error', 'No se pudo actualizar la asociación de servicio {id}.', ['id' => $id]);

            return redirect()->to(site_url('servicios'))
                ->withInput()
                ->with('formData', $submittedData)
                ->with('editAssociationId', $id)
                ->with('error', 'No se pudo actualizar la asociación. Revisá los datos e intentá nuevamente.');
        }

        return redirect()->to(site_url('servicios'))
            ->with('success', 'Los datos de la asociación se actualizaron correctamente.');
    }

    public function darDeBajaAsociacion(int $id): RedirectResponse
    {
        $modelo = new InmuebleServicioModel();
        if ($modelo->find($id) === null) {
            return redirect()->to(site_url('servicios'))
                ->with('error', 'No se encontró la asociación que intentás dar de baja.');
        }

        $db = db_connect();
        $db->transStart();
        $modelo->update($id, ['estado' => 'Inactivo']);
        $db->transComplete();

        if (! $db->transStatus()) {
            log_message('error', 'No se pudo dar de baja la asociación de servicio {id}.', ['id' => $id]);

            return redirect()->to(site_url('servicios'))
                ->with('error', 'No se pudo dar de baja la asociación. Intentá nuevamente.');
        }

        return redirect()->to(site_url('servicios'))
            ->with('success', 'La asociación se dio de baja y se conservó su historial.');
    }
}

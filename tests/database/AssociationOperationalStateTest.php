<?php

use App\Database\Migrations\AddEstadoVinculacion;
use App\Database\Migrations\AddProvinciaToDirecciones;
use App\Database\Migrations\AddUserManagementState;
use App\Database\Migrations\CreateServiInmuebleSchema;
use App\Database\Migrations\ConvertirEstadoActivoInmuebles;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Security\Exceptions\SecurityException;
use Config\Database;

require_once APPPATH . 'Database/Migrations/2026-11-10-214318_CreateServiInmuebleSchema.php';
require_once APPPATH . 'Database/Migrations/2026-11-14-020000_AddUserManagementState.php';
require_once APPPATH . 'Database/Migrations/2026-11-17-190000_AddProvinciaToDirecciones.php';
require_once APPPATH . 'Database/Migrations/2026-11-18-180000_AddEstadoVinculacion.php';
require_once APPPATH . 'Database/Migrations/2026-11-18-190000_ConvertirEstadoActivoInmuebles.php';

/**
 * @internal
 */
final class AssociationOperationalStateTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = db_connect('tests');
        $forge = Database::forge($this->db);
        (new CreateServiInmuebleSchema($forge))->up();
        (new AddUserManagementState($forge))->up();
        (new AddProvinciaToDirecciones($forge))->up();
        (new AddEstadoVinculacion($forge))->up();
        (new ConvertirEstadoActivoInmuebles($forge))->up();

        foreach ([1, 2] as $id) {
            $this->db->table('usuarios')->insert([
                'id' => $id, 'nombre' => 'Propietario', 'apellido' => 'Prueba',
                'email' => "owner{$id}@example.test", 'password' => 'test',
                'activo' => 1, 'estado_solicitud' => 'Aprobada',
            ]);
            $this->db->table('rol_usuario')->insert(['usuario_id' => $id, 'rol_id' => 2]);
            $this->db->table('direcciones')->insert(['id' => $id, 'calle' => 'Calle', 'numero' => '1', 'ciudad' => 'Localidad']);
            $this->db->table('inmuebles')->insert([
                'id' => $id, 'usuario_id' => $id, 'direccion_id' => $id, 'alias_nombre' => "Casa {$id}",
                'tipo' => 'Casa', 'superficie' => 100, 'estado' => 'Disponible', 'estado_actividad' => 'Activo',
            ]);
        }
        $this->db->table('servicios')->insert([
            'id' => 1, 'nombre' => 'Agua', 'unidad_medida_std' => 'm3', 'proveedor' => 'Prueba', 'categoria' => 'Agua',
        ]);
        foreach ([1, 2] as $id) {
            $this->db->table('inmueble_servicios')->insert([
                'id' => $id, 'inmueble_id' => $id, 'servicio_id' => 1, 'titular_inmueble' => 'Prueba',
                'estado' => 'Activo', 'estado_vinculacion' => 'Pausado',
            ]);
        }
    }

    protected function tearDown(): void
    {
        $this->db->close();
        parent::tearDown();
    }

    public function testTransitionsRespectPropertyOwnershipAndLogicalDeletion(): void
    {
        $this->changeState(1, 'En Revisión');
        $this->assertSame('En Revisión', $this->association(1)['estado_vinculacion']);
        $this->changeState(1, 'Inactivo');
        $this->assertSame('En Revisión', $this->association(1)['estado_vinculacion']);

        $this->db->table('inmuebles')->where('id', 1)->update(['estado_actividad' => 'Inactivo']);
        $this->changeState(1, 'Activo');
        $this->assertSame('En Revisión', $this->association(1)['estado_vinculacion']);
        $this->assertArrayHasKey('estado_vinculacion', session()->getFlashdata('errors'));

        $this->changeState(1, 'Pausado');
        $this->assertSame('Pausado', $this->association(1)['estado_vinculacion']);
        $this->changeState(2, 'Activo');
        $this->assertSame('Pausado', $this->association(2)['estado_vinculacion']);

        $token = $this->tokenFor('servicios/asociaciones');
        $this->withSession(['user_id' => 1])->withHeaders(['Cookie' => 'csrf_cookie_name=' . $token])
            ->post('servicios/asociaciones/1/baja', ['csrf_test_name' => $token]);
        $this->assertSame('Inactivo', $this->association(1)['estado']);
        $this->assertSame('Pausado', $this->association(1)['estado_vinculacion']);
        $this->changeState(1, 'En Revisión');
        $this->assertSame('Pausado', $this->association(1)['estado_vinculacion']);
    }

    public function testOnlyActiveOwnedLinksCanBeUsedForNewInvoices(): void
    {
        $this->db->table('inmueble_servicios')->where('id', 1)->update(['estado_vinculacion' => 'Activo']);
        $form = $this->withSession(['user_id' => 1])->get('facturas/nueva');
        $form->assertOK();
        $form->assertSee('Casa 1');
        $form->assertDontSee('Casa 2');
        foreach (['Pausado', 'En Revisión'] as $state) {
            $this->db->table('inmueble_servicios')->where('id', 1)->update(['estado_vinculacion' => $state]);
            $response = $this->postInvoice(1);
            $response->assertRedirectTo(site_url('facturas/nueva'));
            $this->assertSame(0, $this->db->table('facturas')->countAllResults());
            $this->assertArrayHasKey('inmueble_servicio_id', session()->getFlashdata('invoiceErrors'));
        }

        $this->db->table('inmueble_servicios')->where('id', 2)->update(['estado_vinculacion' => 'Activo']);
        $this->postInvoice(2)->assertRedirectTo(site_url('facturas/nueva'));
        $this->assertSame(0, $this->db->table('facturas')->countAllResults());

        $this->db->table('inmueble_servicios')->where('id', 1)->update(['estado_vinculacion' => 'Activo']);
        $this->db->table('inmuebles')->where('id', 1)->update(['estado_actividad' => 'Inactivo']);
        $this->postInvoice(1)->assertRedirectTo(site_url('facturas/nueva'));
        $this->assertSame(0, $this->db->table('facturas')->countAllResults());
        $this->db->table('inmuebles')->where('id', 1)->update(['estado_actividad' => 'Activo']);
        $this->db->table('inmueble_servicios')->where('id', 1)->update(['estado' => 'Inactivo']);
        $this->postInvoice(1)->assertRedirectTo(site_url('facturas/nueva'));
        $this->assertSame(0, $this->db->table('facturas')->countAllResults());
        $this->db->table('inmueble_servicios')->where('id', 1)->update(['estado' => 'Activo']);
        $this->postInvoice(1)->assertRedirectTo(site_url('facturas'));
        $this->assertSame(1, $this->db->table('facturas')->countAllResults());
        $this->db->table('inmueble_servicios')->where('id', 1)->update(['estado_vinculacion' => 'Pausado']);
        $this->withSession(['user_id' => 1])->get('facturas/1')->assertSee('Casa 1');
        $this->assertSame(1, $this->db->table('facturas')->countAllResults());
    }

    public function testNewLinksCannotBeActivatedOnInactiveProperties(): void
    {
        $this->db->table('servicios')->insert([
            'id' => 2, 'nombre' => 'Gas', 'unidad_medida_std' => 'm3', 'proveedor' => 'Prueba', 'categoria' => 'Gas',
        ]);
        $this->db->table('inmuebles')->where('id', 1)->update(['estado_actividad' => 'Inactivo']);

        foreach (['Activo', 'Inactivo'] as $state) {
            $this->createAssociation($state);
            $this->assertSame(0, $this->db->table('inmueble_servicios')->where('servicio_id', 2)->countAllResults());
            $this->assertArrayHasKey('estado_vinculacion', session()->getFlashdata('errors'));
        }

        $this->createAssociation('Pausado');
        $created = $this->db->table('inmueble_servicios')->where('servicio_id', 2)->get()->getRowArray();
        $this->assertSame('Activo', $created['estado']);
        $this->assertSame('Pausado', $created['estado_vinculacion']);
    }

    public function testPrivateRouteAndStateChangeRequireSessionAndCsrf(): void
    {
        $this->withSession([])->get('servicios/asociaciones')->assertRedirectTo(base_url());

        try {
            $this->withSession(['user_id' => 1])->post('servicios/asociaciones/1', [
                'titular_inmueble' => 'Prueba', 'estado_vinculacion' => 'Activo',
            ]);
            $this->fail('El cambio sin CSRF debe ser rechazado.');
        } catch (SecurityException $exception) {
            $this->assertSame('Pausado', $this->association(1)['estado_vinculacion']);
        }
    }

    private function tokenFor(string $path): string
    {
        $response = $this->withSession(['user_id' => 1])->get($path);
        $response->assertOK();
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $response->response()->getBody(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);

        return $matches[1];
    }

    private function changeState(int $id, string $state): void
    {
        $token = $this->tokenFor('servicios/asociaciones');
        $this->withSession(['user_id' => 1])->withHeaders(['Cookie' => 'csrf_cookie_name=' . $token])
            ->post('servicios/asociaciones/' . $id, [
                'csrf_test_name' => $token, 'titular_inmueble' => 'Prueba', 'estado_vinculacion' => $state,
            ])->assertRedirectTo(site_url('servicios/asociaciones'));
    }

    private function postInvoice(int $id): \CodeIgniter\Test\TestResponse
    {
        $token = $this->tokenFor('servicios/asociaciones');
        return $this->withSession(['user_id' => 1])->withHeaders(['Cookie' => 'csrf_cookie_name=' . $token])
            ->post('facturas', [
                'csrf_test_name' => $token, 'inmueble_servicio_id' => (string) $id,
                'periodo_facturacion' => '2026-11', 'fecha_emision' => '2026-11-01',
                'fecha_primer_vencimiento' => '2026-11-20', 'monto_total' => '100',
                'consumo_periodo' => '1',
            ]);
    }

    private function createAssociation(string $state): void
    {
        $token = $this->tokenFor('servicios/asociaciones');
        $this->withSession(['user_id' => 1])->withHeaders(['Cookie' => 'csrf_cookie_name=' . $token])
            ->post('servicios/asociar', [
                'csrf_test_name' => $token, 'servicio_id' => '2', 'inmueble_id' => '1',
                'titular_inmueble' => 'Prueba', 'estado_vinculacion' => $state,
            ])->assertRedirectTo(site_url('servicios/asociaciones'));
    }

    private function association(int $id): array
    {
        return $this->db->table('inmueble_servicios')->where('id', $id)->get()->getRowArray();
    }
}

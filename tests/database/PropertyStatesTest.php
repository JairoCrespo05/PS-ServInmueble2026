<?php

use App\Database\Migrations\AddEstadoVinculacion;
use App\Database\Migrations\AddProvinciaToDirecciones;
use App\Database\Migrations\AddUserManagementState;
use App\Database\Migrations\CreateServiInmuebleSchema;
use App\Database\Migrations\ConvertirEstadoActivoInmuebles;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;
use Config\Services;

require_once APPPATH . 'Database/Migrations/2026-11-10-214318_CreateServiInmuebleSchema.php';
require_once APPPATH . 'Database/Migrations/2026-11-14-020000_AddUserManagementState.php';
require_once APPPATH . 'Database/Migrations/2026-11-17-190000_AddProvinciaToDirecciones.php';
require_once APPPATH . 'Database/Migrations/2026-11-18-180000_AddEstadoVinculacion.php';
require_once APPPATH . 'Database/Migrations/2026-11-18-190000_ConvertirEstadoActivoInmuebles.php';

/**
 * @internal
 */
final class PropertyStatesTest extends CIUnitTestCase
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
        $this->db->table('usuarios')->insert([
            'id' => 1, 'nombre' => 'Propietario', 'apellido' => 'Prueba',
            'email' => 'property-state@example.test', 'password' => 'test',
            'activo' => 1, 'estado_solicitud' => 'Aprobada',
        ]);
        $this->db->table('rol_usuario')->insert(['usuario_id' => 1, 'rol_id' => 2]);
        $this->db->table('direcciones')->insert(['id' => 1, 'calle' => 'Calle', 'numero' => '1', 'ciudad' => 'Localidad']);
        $this->db->table('inmuebles')->insert([
            'id' => 1, 'usuario_id' => 1, 'direccion_id' => 1, 'alias_nombre' => 'Casa',
            'tipo' => 'Casa', 'superficie' => 100, 'estado' => 'Desconocido', 'estado_actividad' => 'Inactivo',
        ]);

        $cache = $this->createMock(CacheInterface::class);
        $provincesKey = 'georef_' . hash('sha256', 'provincias' . serialize(['campos' => 'id,nombre', 'orden' => 'nombre', 'max' => 100]));
        $localitiesKey = 'georef_' . hash('sha256', 'localidades' . serialize(['campos' => 'id,nombre', 'orden' => 'nombre', 'provincia' => '02', 'max' => 5000]));
        $cache->method('get')->willReturnCallback(static fn (string $key): ?array => match ($key) {
            $provincesKey => ['provincias' => [['id' => '02', 'nombre' => 'Provincia de prueba']]],
            $localitiesKey => ['localidades' => [['nombre' => 'Localidad de prueba']]],
            default => null,
        });
        Services::injectMock('cache', $cache);
    }

    protected function tearDown(): void
    {
        Services::resetSingle('cache');
        $this->db->close();
        parent::tearDown();
    }

    public function testOnlyThreeStatesAreAcceptedOnCreateAndUpdate(): void
    {
        foreach (['Activo', 'Inactivo'] as $state) {
            $this->postProperty('inmuebles', ['estado' => $state]);
            $this->assertArrayHasKey('estado', session()->getFlashdata('errors'));
            $this->assertSame(1, $this->db->table('inmuebles')->countAllResults());
        }

        foreach (['Disponible', 'Ocupado', 'En Mantenimiento'] as $state) {
            $this->postProperty('inmuebles', ['estado' => $state, 'alias_nombre' => $state]);
            $this->assertSame($state, $this->db->table('inmuebles')->where('alias_nombre', $state)->get()->getRow()->estado);
        }

        $this->postProperty('inmuebles/1', ['estado' => 'Inactivo', 'reactivar_inmueble' => '1']);
        $this->assertArrayHasKey('estado', session()->getFlashdata('errors'));
        $this->assertSame('Desconocido', $this->propertyState());
    }

    public function testReactivationRequiresExplicitConfirmation(): void
    {
        $this->postProperty('inmuebles/1', ['estado' => 'Disponible']);
        $this->assertSame('Desconocido', $this->propertyState());
        $this->assertArrayHasKey('reactivar_inmueble', session()->getFlashdata('errors'));

        $this->postProperty('inmuebles/1', ['estado' => '', 'reactivar_inmueble' => '1']);
        $this->assertSame('Desconocido', $this->propertyState());
        $this->assertArrayHasKey('estado', session()->getFlashdata('errors'));

        $this->postProperty('inmuebles/1', ['estado' => 'Ocupado', 'reactivar_inmueble' => '1']);
        $this->assertSame('Ocupado', $this->propertyState());
        $this->assertSame('Activo', $this->db->table('inmuebles')->where('id', 1)->get()->getRow()->estado_actividad);
    }

    public function testDeactivationPreservesStateAndRequiresConfirmationToReactivate(): void
    {
        $this->db->table('inmuebles')->where('id', 1)->update(['estado' => 'Disponible', 'estado_actividad' => 'Activo']);
        $page = $this->withSession(['user_id' => 1])->get('servicios/asociaciones');
        $page->assertOK();
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $page->response()->getBody(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);
        $this->withSession(['user_id' => 1])->withHeaders(['Cookie' => 'csrf_cookie_name=' . $matches[1]])
            ->post('inmuebles/1/baja', ['csrf_test_name' => $matches[1]])->assertRedirectTo(site_url('inmuebles'));
        $row = $this->db->table('inmuebles')->where('id', 1)->get()->getRowArray();
        $this->assertSame('Disponible', $row['estado']);
        $this->assertSame('Inactivo', $row['estado_actividad']);
        $this->postProperty('inmuebles/1', ['estado' => 'Disponible']);
        $this->assertSame('Inactivo', $this->db->table('inmuebles')->where('id', 1)->get()->getRow()->estado_actividad);
        $this->postProperty('inmuebles/1', ['estado' => 'Disponible', 'reactivar_inmueble' => '1']);
        $this->assertSame('Activo', $this->db->table('inmuebles')->where('id', 1)->get()->getRow()->estado_actividad);
    }

    private function postProperty(string $path, array $values): void
    {
        $page = $this->withSession(['user_id' => 1])->get('servicios/asociaciones');
        $page->assertOK();
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $page->response()->getBody(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);
        $data = array_merge([
            'alias_nombre' => 'Casa', 'tipo' => 'Casa', 'superficie' => '100',
            'estado' => 'Disponible', 'calle' => 'Calle', 'numero' => '1',
            'provincia' => 'Provincia de prueba', 'ciudad' => 'Localidad de prueba',
            'csrf_test_name' => $matches[1],
        ], $values);
        $this->withSession(['user_id' => 1])->withHeaders(['Cookie' => 'csrf_cookie_name=' . $matches[1]])
            ->post($path, $data)->assertRedirectTo(site_url('inmuebles'));
    }

    private function propertyState(): ?string
    {
        return $this->db->table('inmuebles')->where('id', 1)->get()->getRow()->estado;
    }
}

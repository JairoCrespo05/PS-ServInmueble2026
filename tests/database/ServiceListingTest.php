<?php

use App\Models\RolModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class ServiceListingTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    public function testOwnerSeesOnlyAssociatedServicesButCanAssociateAnUnusedType(): void
    {
        $ownerId = $this->createOwner('service-list-owner@example.test');
        $propertyId = $this->createProperty($ownerId, 'Inmueble con servicio');
        $associatedServiceId = $this->createService('Servicio asociado de prueba');
        $unusedServiceId = $this->createService('Servicio sin asociacion de prueba');

        $this->db->table('inmueble_servicios')->insert([
            'inmueble_id'      => $propertyId,
            'servicio_id'      => $associatedServiceId,
            'titular_inmueble' => 'Titular de prueba',
            'estado'           => 'Activo',
            'estado_vinculacion' => 'Activo',
        ]);

        $response = $this->withSession(['user_id' => $ownerId])->get('servicios/asociaciones');
        $response->assertOK();

        $xpath = $this->xpathFor($response->response()->getBody());
        $cards = $xpath->query('//article[@data-service-card]');
        $this->assertNotFalse($cards);
        $this->assertSame(1, $cards->length);

        $serviceName = $xpath->query('.//h2[@class="service-name"]', $cards->item(0));
        $this->assertNotFalse($serviceName);
        $this->assertSame('Servicio asociado de prueba', trim($serviceName->item(0)->textContent));
        $this->assertSame(
            1,
            $xpath->query('//select[@id="associate-service"]/option[@value="' . $unusedServiceId . '"]')->length
        );
        $this->assertSame(
            'Servicio sin asociacion de prueba — Proveedor de prueba',
            trim($xpath->query('//select[@id="associate-service"]/option[@value="' . $unusedServiceId . '"]')->item(0)->textContent)
        );
        $this->assertSame(
            'Inmueble con servicio — Calle de prueba 123, Ciudad de prueba (Activo)',
            trim($xpath->query('//select[@id="associate-property"]/option[@value="' . $propertyId . '"]')->item(0)->textContent)
        );
    }

    public function testOwnerWithoutAssociationsSeesEmptyStateAndCanStartFirstAssociation(): void
    {
        $ownerId = $this->createOwner('empty-service-list-owner@example.test');
        $this->createProperty($ownerId, 'Inmueble sin servicio');
        $serviceId = $this->createService('Servicio disponible de prueba');

        $response = $this->withSession(['user_id' => $ownerId])->get('servicios/asociaciones');
        $response->assertOK();

        $xpath = $this->xpathFor($response->response()->getBody());
        $this->assertSame(0, $xpath->query('//article[@data-service-card]')->length);
        $this->assertSame(1, $xpath->query('//*[@id="open-associate"]')->length);
        $this->assertSame(
            1,
            $xpath->query('//select[@id="associate-service"]/option[@value="' . $serviceId . '"]')->length
        );
        $this->assertSame(1, $xpath->query('//*[@id="empty-state" and contains(@class, "visible")]')->length);
    }

    public function testCreatingAssociationRedirectsBackToTheAssociationsPage(): void
    {
        $ownerId = $this->createOwner('save-service-owner@example.test');
        $propertyId = $this->createProperty($ownerId, 'Inmueble para asociar');
        $serviceId = $this->createService('Servicio para asociar');

        $page = $this->withSession(['user_id' => $ownerId])->get('servicios/asociaciones');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $page->response()->getBody(), $tokenMatches);
        $this->assertNotEmpty($tokenMatches[1] ?? null);

        $response = $this->withSession(['user_id' => $ownerId])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
            ->post('servicios/asociar', [
                'servicio_id'       => $serviceId,
                'inmueble_id'       => $propertyId,
                'titular_inmueble'  => 'Titular de prueba',
                'estado_vinculacion' => 'Activo',
                'service_locked'    => '0',
                'csrf_test_name'    => $tokenMatches[1],
            ]);

        $response->assertRedirectTo(site_url('servicios/asociaciones'));
        $this->assertSame(
            1,
            $this->db->table('inmueble_servicios')
                ->where('servicio_id', $serviceId)
                ->where('inmueble_id', $propertyId)
                ->countAllResults()
        );

        $updatedPage = $this->withSession(['user_id' => $ownerId])->get('servicios/asociaciones');
        $updatedPage->assertOK();
        $updatedXpath = $this->xpathFor($updatedPage->response()->getBody());
        $this->assertSame(1, $updatedXpath->query('//article[@data-service-card]')->length);
        $this->assertSame(
            'Servicio para asociar',
            trim($updatedXpath->query('//article[@data-service-card]//h2[@class="service-name"]')->item(0)->textContent)
        );
    }

    public function testAdministratorCreatesServicesWithUnitsDeterminedByCategory(): void
    {
        $adminId = $this->createAdministrator();
        $units = [
            'Agua' => 'm3',
            'Electricidad' => 'kWh',
            'Gas' => 'm3',
            'Internet' => 'Mes',
            'Telefonía' => 'Mes',
            'Televisión' => 'Mes',
            'Expensas' => 'Mes',
        ];

        foreach ($units as $category => $unit) {
            $page = $this->withSession(['user_id' => $adminId])->get('tipos-servicio');
            $page->assertOK();
            $html = $page->response()->getBody();
            $this->assertStringContainsString(
                'value="' . esc($category, 'attr') . '" data-unit="' . $unit . '"',
                $html
            );
            $this->assertStringContainsString('id="service-unit" name="unidad_medida_std"', $html);
            preg_match('/name="csrf_test_name" value="([^"]+)"/', $html, $tokenMatches);
            $this->assertNotEmpty($tokenMatches[1] ?? null);

            $data = [
                'nombre' => 'Prueba ' . $category,
                'categoria' => $category,
                'proveedor' => 'Proveedor de prueba',
                'csrf_test_name' => $tokenMatches[1],
            ];
            if ($category !== 'Agua') {
                $data['unidad_medida_std'] = 'Unidad manipulada';
            }
            $response = $this->withSession(['user_id' => $adminId])
                ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
                ->post('tipos-servicio', $data);
            $response->assertRedirectTo(site_url('tipos-servicio'));
            $service = $this->db->table('servicios')->where('nombre', 'Prueba ' . $category)->get()->getRowArray();
            $this->assertNotNull($service);
            $this->assertSame($unit, $service['unidad_medida_std']);
        }
    }

    public function testInvalidCategoryIsRejectedAndEditKeepsExistingUnits(): void
    {
        $adminId = $this->createAdministrator();
        foreach (['Otro' => 'Seleccioná una categoría válida.', '' => 'Ingresá la categoría.'] as $category => $message) {
            $page = $this->withSession(['user_id' => $adminId])->get('tipos-servicio');
            preg_match('/name="csrf_test_name" value="([^"]+)"/', $page->response()->getBody(), $tokenMatches);
            $this->assertNotEmpty($tokenMatches[1] ?? null);

            $response = $this->withSession(['user_id' => $adminId])
                ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
                ->post('tipos-servicio', [
                    'nombre' => 'Categoría inválida',
                    'categoria' => $category,
                    'unidad_medida_std' => 'm3',
                    'proveedor' => 'Proveedor de prueba',
                    'csrf_test_name' => $tokenMatches[1],
                ]);
            $response->assertRedirectTo(site_url('tipos-servicio'));
            $this->assertSame(0, $this->db->table('servicios')->where('nombre', 'Categoría inválida')->countAllResults());
            $this->assertSame($message, session()->getFlashdata('tipoServicioErrors')['categoria']);
        }

        $serviceId = $this->createService('Servicio a modificar');
        $page = $this->withSession(['user_id' => $adminId])->get('tipos-servicio');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $page->response()->getBody(), $tokenMatches);
        $this->assertNotEmpty($tokenMatches[1] ?? null);
        $response = $this->withSession(['user_id' => $adminId])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
            ->post('tipos-servicio/' . $serviceId, [
                'nombre' => 'Servicio a modificar',
                'categoria' => 'Gas',
                'unidad_medida_std' => 'kg',
                'proveedor' => 'Proveedor de prueba',
                'csrf_test_name' => $tokenMatches[1],
            ]);
        $response->assertRedirectTo(site_url('tipos-servicio'));
        $service = $this->db->table('servicios')->where('id', $serviceId)->get()->getRowArray();
        $this->assertSame('kg', $service['unidad_medida_std']);
    }

    public function testPropertySurfaceMinimumIsEnforcedOnCreateAndUpdate(): void
    {
        $ownerId = $this->createOwner('surface-owner@example.test');
        $data = [
            'alias_nombre' => 'Inmueble de prueba',
            'tipo' => 'Casa',
            'estado' => 'Activo',
            'calle' => 'Calle de prueba',
            'numero' => '123',
            'piso_dpto' => '',
            'ciudad' => 'Ciudad de prueba',
        ];

        foreach (['49.99', '50', '50.01'] as $surface) {
            $home = $this->withSession(['user_id' => $ownerId])->get('home');
            preg_match('/name="csrf_test_name" value="([^"]+)"/', $home->response()->getBody(), $tokenMatches);
            $this->assertNotEmpty($tokenMatches[1] ?? null);
            $response = $this->withSession(['user_id' => $ownerId])
                ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
                ->post('inmuebles', array_merge($data, [
                    'alias_nombre' => 'Inmueble ' . $surface,
                    'superficie' => $surface,
                    'csrf_test_name' => $tokenMatches[1],
                ]));
            $response->assertRedirectTo(site_url('inmuebles'));
            $property = $this->db->table('inmuebles')->where('alias_nombre', 'Inmueble ' . $surface)->get()->getRowArray();
            if ($surface === '49.99') {
                $this->assertNull($property);
                $this->assertSame('La superficie debe ser de al menos 50 m².', session()->getFlashdata('errors')['superficie']);
            } else {
                $this->assertNotNull($property);
                $this->assertEquals((float) $surface, (float) $property['superficie']);
            }
        }

        $property = $this->db->table('inmuebles')->where('alias_nombre', 'Inmueble 50')->get()->getRowArray();
        $home = $this->withSession(['user_id' => $ownerId])->get('home');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $home->response()->getBody(), $tokenMatches);
        $this->assertNotEmpty($tokenMatches[1] ?? null);
        $response = $this->withSession(['user_id' => $ownerId])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
            ->post('inmuebles/' . $property['id'], array_merge($data, [
                'superficie' => '49.99',
                'csrf_test_name' => $tokenMatches[1],
            ]));
        $response->assertRedirectTo(site_url('inmuebles'));
        $this->assertSame('La superficie debe ser de al menos 50 m².', session()->getFlashdata('errors')['superficie']);
        $updated = $this->db->table('inmuebles')->where('id', $property['id'])->get()->getRowArray();
        $this->assertEquals(50.0, (float) $updated['superficie']);

        $home = $this->withSession(['user_id' => $ownerId])->get('home');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $home->response()->getBody(), $tokenMatches);
        $this->assertNotEmpty($tokenMatches[1] ?? null);
        $response = $this->withSession(['user_id' => $ownerId])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
            ->post('inmuebles/' . $property['id'], array_merge($data, [
                'superficie' => '50.01',
                'csrf_test_name' => $tokenMatches[1],
            ]));
        $response->assertRedirectTo(site_url('inmuebles'));
        $updated = $this->db->table('inmuebles')->where('id', $property['id'])->get()->getRowArray();
        $this->assertEquals(50.01, (float) $updated['superficie']);
    }

    private function createAdministrator(): int
    {
        $result = (new UserModel())->registerWithDefaultRole([
            'nombre' => 'Admin',
            'apellido' => 'De Prueba',
            'email' => 'service-admin@example.test',
            'password' => password_hash('Passw0rd!', PASSWORD_DEFAULT),
            'telefono' => '',
        ]);
        $this->assertSame(UserModel::REGISTRATION_SUCCESS, $result['status']);
        $this->db->table('rol_usuario')->where('usuario_id', $result['userId'])->update([
            'rol_id' => (new RolModel())->getIdByName(RolModel::ROLE_ADMINISTRATOR),
        ]);
        $this->db->table('usuarios')->where('id', $result['userId'])->update([
            'estado_solicitud' => UserModel::REQUEST_APPROVED,
        ]);

        return $result['userId'];
    }

    private function createOwner(string $email): int
    {
        $result = (new UserModel())->registerWithDefaultRole([
            'nombre'   => 'Propietario',
            'apellido' => 'De Prueba',
            'email'    => $email,
            'password' => password_hash('Passw0rd!', PASSWORD_DEFAULT),
            'telefono' => '',
        ]);

        $this->assertSame(UserModel::REGISTRATION_SUCCESS, $result['status']);
        $this->assertSame(
            UserModel::APPROVAL_SUCCESS,
            (new UserModel())->approvePendingUser($result['userId'], RolModel::ROLE_OWNER)
        );

        return $result['userId'];
    }

    private function createProperty(int $ownerId, string $alias): int
    {
        $this->db->table('direcciones')->insert([
            'calle'  => 'Calle de prueba',
            'numero' => '123',
            'ciudad' => 'Ciudad de prueba',
        ]);
        $addressId = (int) $this->db->insertID();

        $this->db->table('inmuebles')->insert([
            'alias_nombre' => $alias,
            'tipo'         => 'Casa',
            'superficie'   => 80,
            'estado'       => 'Activo',
            'usuario_id'   => $ownerId,
            'direccion_id' => $addressId,
        ]);

        return (int) $this->db->insertID();
    }

    private function createService(string $name): int
    {
        $this->db->table('servicios')->insert([
            'nombre'            => $name,
            'unidad_medida_std' => 'kWh',
            'proveedor'         => 'Proveedor de prueba',
            'categoria'         => 'Electricidad',
        ]);

        return (int) $this->db->insertID();
    }

    private function xpathFor(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $previousErrorSetting = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorSetting);

        return new DOMXPath($document);
    }
}

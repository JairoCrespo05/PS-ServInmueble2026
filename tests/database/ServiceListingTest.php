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
                'estado'            => 'Activo',
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

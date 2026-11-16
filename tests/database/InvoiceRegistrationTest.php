<?php

use App\Models\RolModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class InvoiceRegistrationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    public function testOwnerCanRegisterInvoiceForOwnedServiceAssociation(): void
    {
        $ownerId = $this->createOwner('invoice-owner@example.test');
        $associationId = $this->createServiceAssociation($ownerId);

        $response = $this->postInvoice($ownerId, $associationId, [
            'periodo_facturacion'       => '2026-10',
            'fecha_emision'             => '2026-10-05',
            'fecha_primer_vencimiento'  => '2026-10-20',
            'fecha_segundo_vencimiento' => '',
            'monto_total'               => '1234.50',
            'consumo_periodo'           => '42.125',
            'lectura_anterior'          => '100.000',
            'lectura_actual'            => '142.125',
        ]);

        $response->assertRedirectTo(site_url('facturas'));
        $invoice = $this->db->table('facturas')
            ->where('inmueble_servicio_id', $associationId)
            ->get()
            ->getRowArray();

        $this->assertNotNull($invoice);
        $this->assertSame('2026-10-01', $invoice['periodo_facturacion']);
        $this->assertEqualsWithDelta(1234.5, (float) $invoice['monto_total'], 0.001);
        $this->assertSame('Pendiente', $invoice['estado_factura']);
        $this->assertNull($invoice['fecha_segundo_vencimiento']);
    }

    public function testRegistrationRejectsMissingRequiredFieldsAndIncompleteReadings(): void
    {
        $ownerId = $this->createOwner('invoice-invalid@example.test');
        $associationId = $this->createServiceAssociation($ownerId);

        $response = $this->postInvoice($ownerId, $associationId, [
            'periodo_facturacion'      => '2026-10',
            'fecha_emision'            => '2026-10-05',
            'fecha_primer_vencimiento' => '2026-10-20',
            'monto_total'              => '',
            'consumo_periodo'           => '',
            'lectura_anterior'         => '100',
            'lectura_actual'           => '',
        ]);

        $response->assertRedirectTo(site_url('facturas'));
        $this->assertSame(0, $this->db->table('facturas')->countAllResults());
    }

    public function testRegistrationRejectsSecondInvoiceForSameServiceAndPeriod(): void
    {
        $ownerId = $this->createOwner('invoice-duplicate@example.test');
        $associationId = $this->createServiceAssociation($ownerId);
        $data = [
            'periodo_facturacion'      => '2026-10',
            'fecha_emision'            => '2026-10-05',
            'fecha_primer_vencimiento' => '2026-10-20',
            'monto_total'              => '1234.50',
            'consumo_periodo'           => '42.125',
            'lectura_anterior'         => '',
            'lectura_actual'           => '',
        ];

        $this->postInvoice($ownerId, $associationId, $data)->assertRedirectTo(site_url('facturas'));
        $this->postInvoice($ownerId, $associationId, $data)->assertRedirectTo(site_url('facturas'));

        $this->assertSame(1, $this->db->table('facturas')->countAllResults());
    }

    public function testOwnerCannotRegisterInvoiceForAnotherOwnersService(): void
    {
        $ownerId = $this->createOwner('invoice-authorized@example.test');
        $anotherOwnerId = $this->createOwner('invoice-foreign@example.test');
        $foreignAssociationId = $this->createServiceAssociation($anotherOwnerId);

        $response = $this->postInvoice($ownerId, $foreignAssociationId, [
            'periodo_facturacion'      => '2026-10',
            'fecha_emision'            => '2026-10-05',
            'fecha_primer_vencimiento' => '2026-10-20',
            'monto_total'              => '1234.50',
            'consumo_periodo'           => '42.125',
            'lectura_anterior'         => '',
            'lectura_actual'           => '',
        ]);

        $response->assertRedirectTo(site_url('facturas'));
        $this->assertSame(0, $this->db->table('facturas')->countAllResults());
    }

    private function createOwner(string $email): int
    {
        $user = (new UserModel())->registerWithDefaultRole([
            'nombre'   => 'Propietario',
            'apellido' => 'De Prueba',
            'email'    => $email,
            'password' => password_hash('Passw0rd!', PASSWORD_DEFAULT),
            'telefono' => '',
        ]);

        $this->assertSame(UserModel::REGISTRATION_SUCCESS, $user['status']);
        $this->assertSame(
            UserModel::APPROVAL_SUCCESS,
            (new UserModel())->approvePendingUser($user['userId'], RolModel::ROLE_OWNER)
        );

        return $user['userId'];
    }

    private function createServiceAssociation(int $ownerId): int
    {
        $this->db->table('direcciones')->insert([
            'calle'  => 'Calle de Prueba',
            'numero' => '123',
            'ciudad' => 'Ciudad de Prueba',
        ]);
        $addressId = (int) $this->db->insertID();

        $this->db->table('inmuebles')->insert([
            'alias_nombre' => 'Casa de Prueba',
            'tipo'         => 'Casa',
            'superficie'   => 80,
            'estado'       => 'Activo',
            'usuario_id'   => $ownerId,
            'direccion_id' => $addressId,
        ]);
        $propertyId = (int) $this->db->insertID();

        $this->db->table('servicios')->insert([
            'nombre'            => 'Electricidad',
            'unidad_medida_std' => 'kWh',
            'proveedor'         => 'Proveedor de Prueba',
            'categoria'         => 'Electricidad',
        ]);
        $serviceId = (int) $this->db->insertID();

        $this->db->table('inmueble_servicios')->insert([
            'inmueble_id'      => $propertyId,
            'servicio_id'      => $serviceId,
            'titular_inmueble' => 'Propietario de Prueba',
            'estado'           => 'Activo',
        ]);

        return (int) $this->db->insertID();
    }

    private function postInvoice(int $userId, int $associationId, array $data)
    {
        $form = $this->withSession(['user_id' => $userId])->get('facturas');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $form->response()->getBody(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);

        return $this->withSession(['user_id' => $userId])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $matches[1]])
            ->post('facturas', [
                ...$data,
                'inmueble_servicio_id' => (string) $associationId,
                'csrf_test_name'       => $matches[1],
            ]);
    }
}

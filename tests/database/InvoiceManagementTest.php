<?php

use App\Models\RolModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class InvoiceManagementTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    public function testInvoiceListAndDetailAreLimitedToCurrentOwner(): void
    {
        $ownerId = $this->createOwner('invoice-list-owner@example.test');
        $otherOwnerId = $this->createOwner('invoice-list-other@example.test');
        $ownAssociationId = $this->createServiceAssociation($ownerId, 'Casa Propia');
        $otherAssociationId = $this->createServiceAssociation($otherOwnerId, 'Casa Ajena');
        $ownInvoiceId = $this->createInvoice($ownAssociationId, '2026-10-01');
        $otherInvoiceId = $this->createInvoice($otherAssociationId, '2026-10-01');

        $list = $this->withSession(['user_id' => $ownerId])->get('facturas');
        $list->assertStatus(200);
        $list->assertSee('Casa Propia');
        $list->assertDontSee('Casa Ajena');

        $detail = $this->withSession(['user_id' => $ownerId])->get('facturas/' . $ownInvoiceId);
        $detail->assertStatus(200);
        $detail->assertSee('Electricidad');

        $foreignDetail = $this->withSession(['user_id' => $ownerId])->get('facturas/' . $otherInvoiceId);
        $foreignDetail->assertStatus(404);
    }

    public function testReportSeparatesInvoiceStatesAndShowsOwnerTotals(): void
    {
        $ownerId = $this->createOwner('invoice-report-owner@example.test');
        $otherOwnerId = $this->createOwner('invoice-report-other@example.test');
        $associationId = $this->createServiceAssociation($ownerId, 'Casa Informe');
        $otherAssociationId = $this->createServiceAssociation($otherOwnerId, 'Casa Informe Ajena');
        $this->createInvoice($associationId, '2026-09-01', 'Pendiente', '1200.00');
        $this->createInvoice($associationId, '2026-10-01', 'Pagada', '800.00', [
            'fecha_pago_realizado' => '2026-10-15',
            'monto_abonado' => '800.00',
            'medio_pago' => 'Transferencia',
        ]);
        $this->createInvoice($otherAssociationId, '2026-10-01', 'Pendiente', '9900.00');

        $report = $this->withSession(['user_id' => $ownerId])->get('facturas/informe');

        $report->assertStatus(200);
        $report->assertSee('Facturas pendientes');
        $report->assertSee('Facturas pagadas');
        $report->assertSee('$ 1.200,00');
        $report->assertSee('$ 800,00');
        $report->assertDontSee('Casa Informe Ajena');
    }

    public function testOwnerCanRegisterFullPaymentAndInvoiceBecomesPaid(): void
    {
        $ownerId = $this->createOwner('invoice-payment-owner@example.test');
        $associationId = $this->createServiceAssociation($ownerId, 'Casa Pago');
        $invoiceId = $this->createInvoice($associationId, '2026-10-01', 'Pendiente', '800.00');

        $response = $this->postPayment($ownerId, $invoiceId, [
            'fecha_pago_realizado' => date('Y-m-d'),
            'monto_abonado' => '800.00',
            'medio_pago' => 'Transferencia',
        ]);
        $response->assertRedirectTo(site_url('facturas/' . $invoiceId));

        $invoice = $this->db->table('facturas')->where('id', $invoiceId)->get()->getRowArray();
        $this->assertSame('Pagada', $invoice['estado_factura']);
        $this->assertSame('Transferencia', $invoice['medio_pago']);
        $this->assertSame('800.00', number_format((float) $invoice['monto_abonado'], 2, '.', ''));
    }

    public function testPartialPaymentIsRejectedAndInvoiceRemainsPending(): void
    {
        $ownerId = $this->createOwner('invoice-partial-owner@example.test');
        $associationId = $this->createServiceAssociation($ownerId, 'Casa Pago Parcial');
        $invoiceId = $this->createInvoice($associationId, '2026-10-01', 'Pendiente', '800.00');

        $this->postPayment($ownerId, $invoiceId, [
            'fecha_pago_realizado' => date('Y-m-d'),
            'monto_abonado' => '400.00',
            'medio_pago' => 'Efectivo',
        ])->assertRedirectTo(site_url('facturas/' . $invoiceId));

        $invoice = $this->db->table('facturas')->where('id', $invoiceId)->get()->getRowArray();
        $this->assertSame('Pendiente', $invoice['estado_factura']);
        $this->assertNull($invoice['fecha_pago_realizado']);
    }

    public function testNewInvoiceCanBeRegisteredFromDedicatedForm(): void
    {
        $ownerId = $this->createOwner('invoice-create-owner@example.test');
        $associationId = $this->createServiceAssociation($ownerId, 'Casa Nueva');
        $form = $this->withSession(['user_id' => $ownerId])->get('facturas/nueva');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $form->response()->getBody(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);

        $response = $this->withSession(['user_id' => $ownerId])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $matches[1]])
            ->post('facturas', [
                'csrf_test_name' => $matches[1],
                'inmueble_servicio_id' => (string) $associationId,
                'periodo_facturacion' => '2026-11',
                'fecha_emision' => '2026-11-02',
                'fecha_primer_vencimiento' => '2026-11-20',
                'fecha_segundo_vencimiento' => '',
                'monto_total' => '1234.50',
                'consumo_periodo' => '42.125',
                'lectura_anterior' => '',
                'lectura_actual' => '',
            ]);

        $response->assertRedirectTo(site_url('facturas'));
        $invoice = $this->db->table('facturas')
            ->where('inmueble_servicio_id', $associationId)
            ->get()
            ->getRowArray();

        $this->assertNotNull($invoice);
        $this->assertSame('2026-11-01', $invoice['periodo_facturacion']);
        $this->assertSame('Pendiente', $invoice['estado_factura']);
    }

    public function testNewInvoiceRejectsMissingRequiredValuesAndIncompleteReadings(): void
    {
        $ownerId = $this->createOwner('invoice-invalid-owner@example.test');
        $associationId = $this->createServiceAssociation($ownerId, 'Casa Incompleta');
        $form = $this->withSession(['user_id' => $ownerId])->get('facturas/nueva');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $form->response()->getBody(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);

        $response = $this->withSession(['user_id' => $ownerId])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $matches[1]])
            ->post('facturas', [
                'csrf_test_name' => $matches[1],
                'inmueble_servicio_id' => (string) $associationId,
                'periodo_facturacion' => '2026-11',
                'fecha_emision' => '2026-11-02',
                'fecha_primer_vencimiento' => '2026-11-20',
                'fecha_segundo_vencimiento' => '',
                'monto_total' => '',
                'consumo_periodo' => '',
                'lectura_anterior' => '100',
                'lectura_actual' => '',
            ]);

        $response->assertRedirectTo(site_url('facturas/nueva'));
        $this->assertSame(0, $this->db->table('facturas')->countAllResults());
    }

    public function testNewInvoiceRejectsDuplicateServiceAndPeriod(): void
    {
        $ownerId = $this->createOwner('invoice-duplicate-owner@example.test');
        $associationId = $this->createServiceAssociation($ownerId, 'Casa Duplicada');
        $this->createInvoice($associationId, '2026-11-01');

        $form = $this->withSession(['user_id' => $ownerId])->get('facturas/nueva');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $form->response()->getBody(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);

        $response = $this->withSession(['user_id' => $ownerId])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $matches[1]])
            ->post('facturas', [
                'csrf_test_name' => $matches[1],
                'inmueble_servicio_id' => (string) $associationId,
                'periodo_facturacion' => '2026-11',
                'fecha_emision' => '2026-11-02',
                'fecha_primer_vencimiento' => '2026-11-20',
                'fecha_segundo_vencimiento' => '',
                'monto_total' => '1234.50',
                'consumo_periodo' => '42.125',
                'lectura_anterior' => '',
                'lectura_actual' => '',
            ]);

        $response->assertRedirectTo(site_url('facturas/nueva'));
        $this->assertSame(1, $this->db->table('facturas')->countAllResults());
    }

    private function createOwner(string $email): int
    {
        $user = (new UserModel())->registerWithDefaultRole([
            'nombre' => 'Propietario',
            'apellido' => 'De Prueba',
            'email' => $email,
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

    private function createServiceAssociation(int $ownerId, string $propertyName): int
    {
        $this->db->table('direcciones')->insert([
            'calle' => 'Calle de Prueba',
            'numero' => '123',
            'ciudad' => 'Ciudad de Prueba',
        ]);
        $addressId = (int) $this->db->insertID();

        $this->db->table('inmuebles')->insert([
            'alias_nombre' => $propertyName,
            'tipo' => 'Casa',
            'superficie' => 80,
            'estado' => 'Activo',
            'usuario_id' => $ownerId,
            'direccion_id' => $addressId,
        ]);
        $propertyId = (int) $this->db->insertID();

        $this->db->table('servicios')->insert([
            'nombre' => 'Electricidad',
            'unidad_medida_std' => 'kWh',
            'proveedor' => 'Proveedor de Prueba',
            'categoria' => 'Electricidad',
        ]);
        $serviceId = (int) $this->db->insertID();

        $this->db->table('inmueble_servicios')->insert([
            'inmueble_id' => $propertyId,
            'servicio_id' => $serviceId,
            'titular_inmueble' => 'Propietario de Prueba',
            'estado' => 'Activo',
        ]);

        return (int) $this->db->insertID();
    }

    private function createInvoice(
        int $associationId,
        string $period,
        string $status = 'Pendiente',
        string $amount = '1000.00',
        array $paymentData = []
    ): int {
        $this->db->table('facturas')->insert([
            'inmueble_servicio_id' => $associationId,
            'periodo_facturacion' => $period,
            'fecha_emision' => substr($period, 0, 7) . '-05',
            'fecha_primer_vencimiento' => substr($period, 0, 7) . '-20',
            'fecha_segundo_vencimiento' => null,
            'monto_total' => $amount,
            'consumo_periodo' => '20.000',
            'lectura_anterior' => null,
            'lectura_actual' => null,
            'estado_factura' => $status,
            'fecha_pago_realizado' => $paymentData['fecha_pago_realizado'] ?? null,
            'monto_abonado' => $paymentData['monto_abonado'] ?? null,
            'medio_pago' => $paymentData['medio_pago'] ?? null,
        ]);

        return (int) $this->db->insertID();
    }

    private function postPayment(int $userId, int $invoiceId, array $data)
    {
        $page = $this->withSession(['user_id' => $userId])->get('facturas/' . $invoiceId);
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $page->response()->getBody(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);

        return $this->withSession(['user_id' => $userId])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $matches[1]])
            ->post('facturas/' . $invoiceId . '/pago', [
                ...$data,
                'csrf_test_name' => $matches[1],
            ]);
    }
}

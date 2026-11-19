<?php

use App\Models\FacturaModel;
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

    public function testOwnerHomeShowsRecentInvoicesFromDatabaseOnly(): void
    {
        $ownerId = $this->createOwner('invoice-home-owner@example.test');
        $otherOwnerId = $this->createOwner('invoice-home-other@example.test');
        $associationId = $this->createServiceAssociation($ownerId, 'Casa del Panel');
        $otherAssociationId = $this->createServiceAssociation($otherOwnerId, 'Casa ajena del Panel');
        $paidInvoiceId = $this->createInvoice($associationId, '2026-10-01', 'Pagada', '800.00', [
            'fecha_pago_realizado' => '2026-10-15',
            'monto_abonado' => '800.00',
            'medio_pago' => 'Transferencia',
        ]);
        $pendingInvoiceId = $this->createInvoice($associationId, '2026-11-01', 'Pendiente', '1234.50');
        $this->createInvoice($otherAssociationId, '2026-11-01', 'Pendiente', '9900.00');

        $home = $this->withSession(['user_id' => $ownerId])->get('home');

        $home->assertStatus(200);
        $home->assertSee('Casa del Panel');
        $home->assertSee('Proveedor de Prueba');
        $home->assertSee('Electricidad');
        $home->assertSee('11/2026');
        $home->assertSee('20/11/2026');
        $home->assertSee('$ 1.234,50');
        $home->assertSee('Pendiente');
        $home->assertSee('Pagada');
        $home->assertDontSee('Casa ajena del Panel');
        $home->assertSee(site_url('facturas/' . $pendingInvoiceId) . '#payment-title');
        $home->assertSee(site_url('facturas/' . $paidInvoiceId));

        $body = $home->response()->getBody();
        $this->assertLessThan(strpos($body, '10/2026'), strpos($body, '11/2026'));
    }

    public function testOwnerHomeShowsLiveMetricsScopedToTheOwner(): void
    {
        $ownerId = $this->createOwner('invoice-metrics-owner@example.test');
        $otherOwnerId = $this->createOwner('invoice-metrics-other@example.test');
        $month = new DateTimeImmutable('first day of this month');
        $previousMonth = $month->modify('-1 month');

        $activeAssociationId = $this->createServiceAssociation($ownerId, 'Casa activa métricas');
        $inactivePropertyAssociationId = $this->createServiceAssociation($ownerId, 'Casa inactiva métricas');
        $pausedAssociationId = $this->createServiceAssociation($ownerId, 'Servicio pausado métricas');
        $foreignAssociationId = $this->createServiceAssociation($otherOwnerId, 'Casa ajena métricas');

        $this->db->table('inmuebles')
            ->where('id', $this->propertyIdForAssociation($inactivePropertyAssociationId))
            ->update(['estado_actividad' => 'Inactivo']);
        $this->db->table('inmueble_servicios')
            ->where('id', $pausedAssociationId)
            ->update(['estado_vinculacion' => 'Pausado']);

        $pendingThisMonthId = $this->createInvoice(
            $activeAssociationId,
            $month->format('Y-m-d'),
            'Pendiente',
            '1234.50'
        );
        $pendingOutsideMonthId = $this->createInvoice(
            $inactivePropertyAssociationId,
            $month->format('Y-m-d'),
            'Pendiente',
            '500.00'
        );
        $paidThisMonthId = $this->createInvoice(
            $pausedAssociationId,
            $month->format('Y-m-d'),
            'Pagada',
            '600.00'
        );
        $foreignInvoiceId = $this->createInvoice(
            $foreignAssociationId,
            $month->format('Y-m-d'),
            'Pendiente',
            '9999.00'
        );

        $this->setInvoiceDueDates($pendingThisMonthId, $previousMonth, $month->modify('+5 days'));
        $this->setInvoiceDueDates($pendingOutsideMonthId, $previousMonth);
        $this->setInvoiceDueDates($paidThisMonthId, $month->modify('+5 days'));
        $this->setInvoiceDueDates($foreignInvoiceId, $month->modify('+5 days'));

        $home = $this->withSession(['user_id' => $ownerId])->get('home');

        $home->assertStatus(200);
        $body = $home->response()->getBody();
        $this->assertMatchesRegularExpression('/id="kpiProperties">\s*2\s*<small>propiedades/s', $body);
        $this->assertMatchesRegularExpression('/id="kpiServices">\s*1\s*<small>contratos/s', $body);
        $this->assertMatchesRegularExpression('/id="kpiToPay">\s*\$\s*1\.234,50/s', $body);
        $this->assertMatchesRegularExpression('/1\s*factura pendiente\s*con vencimiento este mes/s', $body);
        $this->assertMatchesRegularExpression('/id="kpiCompliance">\s*33,3%/s', $body);
        $this->assertMatchesRegularExpression('/1\s*de 3 facturas del período pagadas/s', $body);
        $home->assertDontSee('$ 9.999,00');
    }

    public function testOwnerHomeShowsEmptyMetricStateWhenThereIsNoData(): void
    {
        $ownerId = $this->createOwner('invoice-metrics-empty@example.test');

        $home = $this->withSession(['user_id' => $ownerId])->get('home');
        $body = $home->response()->getBody();

        $home->assertStatus(200);
        $home->assertSee('id="kpiCompliance">—');
        $home->assertSee('Sin facturas del período actual');
        $this->assertMatchesRegularExpression('/id="kpiProperties">\s*0\s*<small>propiedades/s', $body);
        $this->assertMatchesRegularExpression('/id="kpiServices">\s*0\s*<small>contratos/s', $body);
        $this->assertMatchesRegularExpression('/>\s*0\s*facturas pendientes\s*con vencimiento este mes/s', $body);
    }

    public function testUpcomingInvoiceBannerShowsNearestPendingDueDateWithinSevenDays(): void
    {
        $ownerId = $this->createOwner('invoice-upcoming-owner@example.test');
        $otherOwnerId = $this->createOwner('invoice-upcoming-other@example.test');
        $associationId = $this->createServiceAssociation($ownerId, 'Casa con vencimientos');
        $otherAssociationId = $this->createServiceAssociation($otherOwnerId, 'Casa ajena con vencimiento');
        $today = new DateTimeImmutable('today');

        $secondDueInvoiceId = $this->createInvoice($associationId, '2026-03-01');
        $firstDueInvoiceId = $this->createInvoice($associationId, '2026-04-01');
        $paidInvoiceId = $this->createInvoice($associationId, '2026-05-01', 'Pagada');
        $overdueInvoiceId = $this->createInvoice($associationId, '2026-06-01');
        $outsideWindowInvoiceId = $this->createInvoice($associationId, '2026-07-01');
        $foreignInvoiceId = $this->createInvoice($otherAssociationId, '2026-08-01');

        $this->setInvoiceDueDates($secondDueInvoiceId, $today->modify('-2 days'), $today->modify('+2 days'));
        $this->setInvoiceDueDates($firstDueInvoiceId, $today->modify('+5 days'));
        $this->setInvoiceDueDates($paidInvoiceId, $today->modify('+1 day'));
        $this->setInvoiceDueDates($overdueInvoiceId, $today->modify('-1 day'));
        $this->setInvoiceDueDates($outsideWindowInvoiceId, $today->modify('+8 days'));
        $this->setInvoiceDueDates($foreignInvoiceId, $today->modify('+1 day'));

        $nearestInvoice = (new FacturaModel())->findNearestUpcomingForOwner($ownerId);

        $this->assertNotNull($nearestInvoice);
        $this->assertSame($secondDueInvoiceId, (int) $nearestInvoice['factura_id']);
        $this->assertSame($today->modify('+2 days')->format('Y-m-d'), $nearestInvoice['proximo_vencimiento']);
        $this->assertSame(2, $nearestInvoice['dias_restantes']);

        $home = $this->withSession(['user_id' => $ownerId])->get('home');
        $home->assertStatus(200);
        $home->assertSee('vence en 2 días');
        $home->assertSee($today->modify('+2 days')->format('d/m/Y'));
        $home->assertSee(
            'id="upcomingInvoiceAction" href="' . site_url('facturas/' . $secondDueInvoiceId . '#payment-title') . '"'
        );
    }

    public function testUpcomingInvoiceDueExactlyInSevenDaysIsIncluded(): void
    {
        $ownerId = $this->createOwner('invoice-seven-day-owner@example.test');
        $associationId = $this->createServiceAssociation($ownerId, 'Casa vence en siete días');
        $invoiceId = $this->createInvoice($associationId, '2026-03-01');
        $dueDate = (new DateTimeImmutable('today'))->modify('+7 days');
        $this->setInvoiceDueDates($invoiceId, $dueDate);

        $home = $this->withSession(['user_id' => $ownerId])->get('home');

        $home->assertStatus(200);
        $home->assertSee('vence en 7 días');
        $home->assertSee($dueDate->format('d/m/Y'));
    }

    public function testUpcomingInvoiceBannerIsHiddenWhenNoPendingInvoiceIsDueWithinSevenDays(): void
    {
        $ownerId = $this->createOwner('invoice-no-upcoming-owner@example.test');
        $associationId = $this->createServiceAssociation($ownerId, 'Casa sin vencimiento próximo');
        $invoiceId = $this->createInvoice($associationId, '2026-03-01');
        $this->setInvoiceDueDates($invoiceId, (new DateTimeImmutable('today'))->modify('+8 days'));

        $home = $this->withSession(['user_id' => $ownerId])->get('home');

        $home->assertStatus(200);
        $home->assertDontSee('Aviso de Próximo Vencimiento');
        $home->assertDontSee('upcomingInvoiceAction');
    }

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

    public function testInvoiceListCanSearchPropertyServiceAndProviderAndFilterStatus(): void
    {
        $ownerId = $this->createOwner('invoice-filter-owner@example.test');
        $otherOwnerId = $this->createOwner('invoice-filter-other@example.test');
        $associationId = $this->createServiceAssociation($ownerId, 'Casa Filtrable');
        $otherAssociationId = $this->createServiceAssociation($otherOwnerId, 'Casa Ajena Filtrable');
        $this->createInvoice($associationId, '2026-11-01', 'Pendiente');
        $this->createInvoice($associationId, '2026-10-01', 'Pagada', '1000.00', [
            'fecha_pago_realizado' => '2026-10-15',
            'monto_abonado' => '1000.00',
            'medio_pago' => 'Transferencia',
        ]);
        $this->createInvoice($otherAssociationId, '2026-11-01', 'Pendiente');

        foreach (['Casa Filtrable', 'Electricidad', 'Proveedor de Prueba'] as $term) {
            $search = $this->withSession(['user_id' => $ownerId])
                ->get('facturas?buscar=' . rawurlencode($term));

            $search->assertStatus(200);
            $search->assertSee('Casa Filtrable');
            $search->assertDontSee('Casa Ajena Filtrable');
        }

        $pending = $this->withSession(['user_id' => $ownerId])
            ->get('facturas?estado=Pendiente');

        $pending->assertStatus(200);
        $pending->assertSee('11/2026');
        $pending->assertDontSee('10/2026');
        $pending->assertDontSee('Casa Ajena Filtrable');

        $noResults = $this->withSession(['user_id' => $ownerId])
            ->get('facturas?buscar=sin-resultados');

        $noResults->assertStatus(200);
        $noResults->assertSee('No hay facturas que coincidan con esos filtros.');
        $noResults->assertSee('Limpiar filtros');
        $noResults->assertDontSee('Todavía no registraste facturas');
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

    public function testInvoiceReportSearchFiltersBothTablesAndKeepsOwnerTotalsGlobal(): void
    {
        $ownerId = $this->createOwner('invoice-report-search-owner@example.test');
        $otherOwnerId = $this->createOwner('invoice-report-search-other@example.test');
        $waterAssociationId = $this->createServiceAssociation($ownerId, 'Casa Mistral', 'Agua', 'Aguas del Sur');
        $electricityAssociationId = $this->createServiceAssociation(
            $ownerId,
            'Departamento Norte',
            'Electricidad',
            'Luz del Sur'
        );
        $otherAssociationId = $this->createServiceAssociation(
            $otherOwnerId,
            'Casa Mistral Ajena',
            'Agua',
            'Aguas del Sur'
        );
        $this->createInvoice($waterAssociationId, '2026-09-01', 'Pendiente', '1200.00');
        $this->createInvoice($waterAssociationId, '2026-10-01', 'Pagada', '800.00', [
            'fecha_pago_realizado' => '2026-10-15',
            'monto_abonado' => '800.00',
            'medio_pago' => 'Transferencia',
        ]);
        $this->createInvoice($electricityAssociationId, '2026-07-01', 'Pendiente', '300.00');
        $this->createInvoice($electricityAssociationId, '2026-08-01', 'Pagada', '400.00', [
            'fecha_pago_realizado' => '2026-08-15',
            'monto_abonado' => '400.00',
            'medio_pago' => 'Efectivo',
        ]);
        $this->createInvoice($otherAssociationId, '2026-11-01', 'Pendiente', '9900.00');

        foreach ([
            'Casa Mistral' => ['Casa Mistral', 'Departamento Norte'],
            'Electricidad' => ['Departamento Norte', 'Casa Mistral'],
            'Luz del Sur' => ['Departamento Norte', 'Casa Mistral'],
        ] as $term => [$expectedProperty, $excludedProperty]) {
            $report = $this->withSession(['user_id' => $ownerId])
                ->get('facturas/informe?buscar=' . rawurlencode($term));

            $report->assertStatus(200);
            $report->assertSee($expectedProperty);
            $report->assertDontSee($excludedProperty);
            $report->assertDontSee('Casa Mistral Ajena');
            $report->assertSee('La búsqueda coincide con 2 de 4 facturas:');
            $report->assertSee('$ 1.500,00');
            $report->assertSee('$ 1.200,00');
        }

        $noResults = $this->withSession(['user_id' => $ownerId])
            ->get('facturas/informe?buscar=sin-resultados');

        $noResults->assertStatus(200);
        $noResults->assertSee('No hay facturas pendientes que coincidan con esta búsqueda.');
        $noResults->assertSee('No hay facturas pagadas que coincidan con esta búsqueda.');
        $noResults->assertSee('$ 1.500,00');
        $noResults->assertSee('$ 1.200,00');
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

    private function createServiceAssociation(
        int $ownerId,
        string $propertyName,
        string $serviceName = 'Electricidad',
        string $provider = 'Proveedor de Prueba'
    ): int
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
            'nombre' => $serviceName,
            'unidad_medida_std' => 'kWh',
            'proveedor' => $provider,
            'categoria' => $serviceName,
        ]);
        $serviceId = (int) $this->db->insertID();

        $this->db->table('inmueble_servicios')->insert([
            'inmueble_id' => $propertyId,
            'servicio_id' => $serviceId,
            'titular_inmueble' => 'Propietario de Prueba',
            'estado' => 'Activo',
            'estado_vinculacion' => 'Activo',
        ]);

        return (int) $this->db->insertID();
    }

    private function propertyIdForAssociation(int $associationId): int
    {
        return (int) $this->db->table('inmueble_servicios')
            ->select('inmueble_id')
            ->where('id', $associationId)
            ->get()
            ->getRow()
            ->inmueble_id;
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

    private function setInvoiceDueDates(int $invoiceId, DateTimeImmutable $firstDueDate, ?DateTimeImmutable $secondDueDate = null): void
    {
        $this->db->table('facturas')
            ->where('id', $invoiceId)
            ->update([
                'fecha_primer_vencimiento' => $firstDueDate->format('Y-m-d'),
                'fecha_segundo_vencimiento' => $secondDueDate?->format('Y-m-d'),
            ]);
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

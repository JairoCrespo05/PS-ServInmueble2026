<?php

use App\Models\AdminDashboardModel;
use App\Models\RolModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AdminDashboardMetricsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    public function testAdministratorDashboardConsolidatesApprovedOwnerMetricsAndOverdueInvoices(): void
    {
        $adminId = $this->createUser('admin-dashboard@example.test', 'Admin', 'Pruebas', RolModel::ROLE_ADMINISTRATOR);
        $ownerId = $this->createUser('dashboard-owner@example.test', 'Ana', 'Propietaria', RolModel::ROLE_OWNER);
        $otherOwnerId = $this->createUser('dashboard-other-owner@example.test', 'Beto', 'Propietario', RolModel::ROLE_OWNER);
        $pendingUserId = $this->createUser('dashboard-pending@example.test', 'Pendiente', 'Pruebas');

        $createdAt = '2025-02-12 12:00:00';
        $activeAssociationId = $this->createAssociation($ownerId, 'Casa activa dashboard', 'Agua', 'Aguas SA', $createdAt);
        $inactivePropertyAssociationId = $this->createAssociation($ownerId, 'Casa baja dashboard', 'Gas', 'Gas SA', $createdAt);
        $inactiveAssociationId = $this->createAssociation($ownerId, 'Casa servicio baja', 'Internet', 'Red SA', $createdAt);
        $otherOwnerAssociationId = $this->createAssociation($otherOwnerId, 'Casa otro propietario', 'Electricidad', 'Luz SA', $createdAt);
        $pendingAssociationId = $this->createAssociation($pendingUserId, 'Casa cuenta pendiente', 'Telefonía', 'Tel SA', $createdAt);

        $this->db->table('inmuebles')
            ->where('id', $this->propertyId($inactivePropertyAssociationId))
            ->update(['estado_actividad' => 'Inactivo', 'estado' => 'Desconocido']);
        $this->db->table('inmueble_servicios')
            ->where('id', $inactiveAssociationId)
            ->update(['estado' => 'Inactivo']);

        $today = new DateTimeImmutable('today');
        $overdueInvoiceId = $this->createInvoice($activeAssociationId, '2025-01-01', 'Pendiente');
        $this->setDueDates($overdueInvoiceId, $today->modify('-10 days'), $today->modify('-2 days'));
        $futureInvoiceId = $this->createInvoice($activeAssociationId, '2025-02-01', 'Pendiente');
        $this->setDueDates($futureInvoiceId, $today->modify('+10 days'));
        $inactivePropertyInvoiceId = $this->createInvoice($inactivePropertyAssociationId, '2025-03-01', 'Pendiente');
        $this->setDueDates($inactivePropertyInvoiceId, $today->modify('-5 days'));
        $paidLateInvoiceId = $this->createInvoice($inactiveAssociationId, '2025-04-01', 'Pagada');
        $this->setDueDates($paidLateInvoiceId, $today->modify('-8 days'));
        $this->db->table('facturas')
            ->where('id', $paidLateInvoiceId)
            ->update(['fecha_pago_realizado' => $today->format('Y-m-d')]);
        $this->createInvoice($otherOwnerAssociationId, '2025-05-01', 'Pendiente');
        $pendingOwnerInvoiceId = $this->createInvoice($pendingAssociationId, '2025-06-01', 'Pendiente');
        $this->setDueDates($pendingOwnerInvoiceId, $today->modify('-20 days'));

        $metrics = (new AdminDashboardModel())->findGlobalMetrics();

        $this->assertSame(2, $metrics['owners']);
        $this->assertSame(3, $metrics['properties_active']);
        $this->assertSame(1, $metrics['properties_inactive']);
        $this->assertSame(2, $metrics['associations_active']);
        $this->assertSame(2, $metrics['associations_inactive']);
        $this->assertSame(4, $metrics['invoices_pending']);
        $this->assertSame(1, $metrics['invoices_paid']);
        $this->assertSame(2, $metrics['invoices_overdue']);
        $this->assertSame(50.0, $metrics['overdue_percentage']);

        $dashboard = (new AdminDashboardModel())->findOverdueInvoiceDetails();
        $this->assertSame(2, $dashboard['total']);
        $this->assertSame(
            [$inactivePropertyInvoiceId, $overdueInvoiceId],
            array_map(static fn (array $invoice): int => (int) $invoice['factura_id'], $dashboard['items'])
        );
        $this->assertSame('Ana', $dashboard['items'][0]['propietario_nombre']);
        $this->assertSame('Gas', $dashboard['items'][0]['servicio_nombre']);
        $this->assertSame('Casa baja dashboard', $dashboard['items'][0]['alias_nombre']);
        $this->assertSame($today->modify('-5 days')->format('Y-m-d'), $dashboard['items'][0]['fecha_vencimiento_efectivo']);
        $this->assertSame($today->modify('-2 days')->format('Y-m-d'), $dashboard['items'][1]['fecha_vencimiento_efectivo']);
        $this->assertSame(2, $dashboard['items'][1]['dias_mora']);

        $home = $this->withSession(['user_id' => $adminId])->get('home');
        $home->assertStatus(200);
        $home->assertSee('Dashboard de administración');
        $home->assertSee('Propietarios aprobados');
        $home->assertSee('Facturas pendientes vencidas');
        $home->assertSee('Casa baja dashboard');
        $home->assertSee('Ana Propietaria');
        $home->assertSee('Servicios asociados');
        $home->assertSee('Tipos de servicio del catálogo');
    }

    public function testChartReturnsMonthlyCountsForApprovedOwnersAssociationsAndCatalogTypes(): void
    {
        $adminId = $this->createUser('chart-admin@example.test', 'Admin', 'Gráfico', RolModel::ROLE_ADMINISTRATOR);
        $ownerId = $this->createUser('chart-owner@example.test', 'Propietario', 'Gráfico', RolModel::ROLE_OWNER);
        $pendingUserId = $this->createUser('chart-pending@example.test', 'Pendiente', 'Gráfico');
        $createdAt = '2025-02-12 12:00:00';
        $this->createAssociation($ownerId, 'Casa gráfico uno', 'Agua', 'Aguas SA', $createdAt);
        $this->createAssociation($ownerId, 'Casa gráfico dos', 'Gas', 'Gas SA', $createdAt);
        $this->createAssociation($pendingUserId, 'Casa gráfico pendiente', 'Luz', 'Luz SA', $createdAt);

        $dashboard = new AdminDashboardModel();
        $associationSeries = $dashboard->findMonthlyChartData(2025, AdminDashboardModel::CHART_ASSOCIATIONS);
        $catalogSeries = $dashboard->findMonthlyChartData(2025, AdminDashboardModel::CHART_SERVICE_TYPES);
        $ownerSeries = $dashboard->findMonthlyChartData(2025, AdminDashboardModel::CHART_OWNERS);

        $this->assertCount(12, $associationSeries['values']);
        $this->assertSame(2, $associationSeries['values'][1]);
        $this->assertSame(0, $associationSeries['values'][0]);
        $this->assertSame(3, $catalogSeries['values'][1]);
        $this->assertSame(1, $ownerSeries['values'][1]);

        $response = $this->withSession(['user_id' => $adminId])
            ->get('admin/dashboard/grafico?year=2025&metric=owners');
        $response->assertStatus(200);
        $responseData = json_decode($response->response()->getBody(), true);
        $this->assertSame(1, $responseData['values'][1]);

        $invalidMetric = $this->withSession(['user_id' => $adminId])
            ->get('admin/dashboard/grafico?year=2025&metric=unknown');
        $invalidMetric->assertStatus(400);

        $ownerResponse = $this->withSession(['user_id' => $ownerId])
            ->get('admin/dashboard/grafico?year=2025&metric=owners');
        $ownerResponse->assertStatus(403);
    }

    public function testDashboardShowsNoDataForMoraWhenThereAreNoPendingInvoices(): void
    {
        $adminId = $this->createUser('empty-dashboard-admin@example.test', 'Admin', 'Vacío', RolModel::ROLE_ADMINISTRATOR);

        $home = $this->withSession(['user_id' => $adminId])->get('home');

        $home->assertStatus(200);
        $home->assertSee('Sin datos');
        $home->assertSee('No hay facturas pendientes vencidas.');
    }

    private function createUser(
        string $email,
        string $firstName,
        string $lastName,
        ?string $role = null
    ): int {
        $result = (new UserModel())->registerWithDefaultRole([
            'nombre' => $firstName,
            'apellido' => $lastName,
            'email' => $email,
            'password' => password_hash('Passw0rd!', PASSWORD_DEFAULT),
            'telefono' => '',
        ]);
        $this->assertSame(UserModel::REGISTRATION_SUCCESS, $result['status']);

        if ($role !== null) {
            $this->assertSame(
                UserModel::APPROVAL_SUCCESS,
                (new UserModel())->approvePendingUser($result['userId'], $role)
            );
        }

        $this->db->table('usuarios')
            ->where('id', $result['userId'])
            ->update(['created_at' => '2025-02-12 12:00:00']);

        return (int) $result['userId'];
    }

    private function createAssociation(
        int $ownerId,
        string $propertyName,
        string $serviceName,
        string $provider,
        string $createdAt
    ): int {
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
            'estado' => 'Disponible',
            'estado_actividad' => 'Activo',
            'usuario_id' => $ownerId,
            'direccion_id' => $addressId,
            'created_at' => $createdAt,
        ]);
        $propertyId = (int) $this->db->insertID();

        $unit = match ($serviceName) {
            'Agua', 'Gas' => 'm3',
            'Electricidad' => 'kWh',
            default => 'Mes',
        };
        $this->db->table('servicios')->insert([
            'nombre' => $serviceName,
            'unidad_medida_std' => $unit,
            'proveedor' => $provider,
            'categoria' => $serviceName,
            'created_at' => $createdAt,
        ]);
        $serviceId = (int) $this->db->insertID();

        $this->db->table('inmueble_servicios')->insert([
            'inmueble_id' => $propertyId,
            'servicio_id' => $serviceId,
            'titular_inmueble' => 'Propietario de Prueba',
            'estado' => 'Activo',
            'estado_vinculacion' => 'Activo',
            'created_at' => $createdAt,
        ]);

        return (int) $this->db->insertID();
    }

    private function propertyId(int $associationId): int
    {
        return (int) $this->db->table('inmueble_servicios')
            ->select('inmueble_id')
            ->where('id', $associationId)
            ->get()
            ->getRow()
            ->inmueble_id;
    }

    private function createInvoice(int $associationId, string $period, string $status): int
    {
        $this->db->table('facturas')->insert([
            'inmueble_servicio_id' => $associationId,
            'periodo_facturacion' => $period,
            'fecha_emision' => substr($period, 0, 7) . '-05',
            'fecha_primer_vencimiento' => substr($period, 0, 7) . '-20',
            'fecha_segundo_vencimiento' => null,
            'monto_total' => '1000.00',
            'estado_factura' => $status,
            'fecha_pago_realizado' => $status === 'Pagada' ? '2025-05-20' : null,
        ]);

        return (int) $this->db->insertID();
    }

    private function setDueDates(int $invoiceId, DateTimeImmutable $firstDueDate, ?DateTimeImmutable $secondDueDate = null): void
    {
        $this->db->table('facturas')
            ->where('id', $invoiceId)
            ->update([
                'fecha_primer_vencimiento' => $firstDueDate->format('Y-m-d'),
                'fecha_segundo_vencimiento' => $secondDueDate?->format('Y-m-d'),
            ]);
    }
}

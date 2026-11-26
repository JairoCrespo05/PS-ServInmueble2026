<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use DateTimeImmutable;
use InvalidArgumentException;

class AdminDashboardModel extends Model
{
    public const CHART_PROPERTIES          = 'properties';
    public const CHART_OWNERS              = 'owners';
    public const CHART_ASSOCIATIONS        = 'associations';
    public const CHART_SERVICE_TYPES       = 'service_types';

    /**
     * @return list<array<string, mixed>>
     */
    public function findPropertyServiceSummary(): array
    {
        $rows = $this->db->table('inmuebles i')
            ->select([
                'i.id AS inmueble_id',
                'i.alias_nombre',
                'i.tipo',
                'i.estado AS estado_inmueble',
                'i.estado_actividad',
                'i.usuario_id',
                'u.nombre AS usuario_nombre',
                'u.apellido AS usuario_apellido',
                'u.activo AS usuario_activo',
                'r.nombre AS usuario_rol',
                'isv.id AS asociacion_id',
                'isv.estado AS estado_asociacion',
                'isv.estado_vinculacion',
                "CASE WHEN i.estado_actividad = 'Inactivo' OR LOWER(TRIM(isv.estado)) = 'inactivo' THEN 'Inactivo' ELSE isv.estado_vinculacion END AS estado_efectivo",
                's.nombre AS servicio_nombre',
                's.categoria AS servicio_categoria',
                's.proveedor AS servicio_proveedor',
            ])
            ->join('usuarios u', 'u.id = i.usuario_id', 'left')
            ->join('rol_usuario ru', 'ru.usuario_id = u.id', 'left')
            ->join('roles r', 'r.id = ru.rol_id', 'left')
            ->join('inmueble_servicios isv', 'isv.inmueble_id = i.id', 'left')
            ->join('servicios s', 's.id = isv.servicio_id', 'left')
            ->orderBy('i.id', 'DESC')
            ->orderBy('isv.id', 'ASC')
            ->get()
            ->getResultArray();

        $properties = [];
        foreach ($rows as $row) {
            $propertyId = (int) $row['inmueble_id'];
            if (! isset($properties[$propertyId])) {
                $hasOwner = $row['usuario_id'] !== null;
                $properties[$propertyId] = [
                    'id' => $propertyId,
                    'alias_nombre' => $row['alias_nombre'],
                    'tipo' => $row['tipo'],
                    'estado' => $row['estado_inmueble'],
                    'estado_actividad' => $row['estado_actividad'],
                    'usuario_nombre' => $hasOwner
                        ? trim((string) $row['usuario_nombre'] . ' ' . (string) $row['usuario_apellido'])
                        : 'Sin usuario asignado',
                    'usuario_rol' => $row['usuario_rol'],
                    'usuario_estado' => ! $hasOwner
                        ? 'Sin asignar'
                        : ((bool) $row['usuario_activo'] ? 'Activa' : 'Baja'),
                    'servicios' => [],
                ];
            }

            if ($row['asociacion_id'] !== null) {
                $properties[$propertyId]['servicios'][] = [
                    'nombre' => $row['servicio_nombre'],
                    'categoria' => $row['servicio_categoria'],
                    'proveedor' => $row['servicio_proveedor'],
                    'estado' => $row['estado_asociacion'],
                    'estado_vinculacion' => $row['estado_vinculacion'],
                    'estado_efectivo' => $row['estado_efectivo'],
                ];
            }
        }

        return array_values($properties);
    }

    /**
     * @return array{
     *     owners: int,
     *     properties_active: int,
     *     properties_inactive: int,
     *     associations_active: int,
     *     associations_inactive: int,
     *     invoices_pending: int,
     *     invoices_paid: int,
     *     invoices_overdue: int,
     *     overdue_percentage: ?float
     * }
     */
    public function findGlobalMetrics(): array
    {
        $owners = $this->scopeApprovedOwners($this->db->table('usuarios u'));
        $ownerCount = (clone $owners)->countAllResults();

        $properties = $this->scopeApprovedOwners(
            $this->db->table('inmuebles i')->join('usuarios u', 'u.id = i.usuario_id')
        );
        $propertyCount = (clone $properties)->countAllResults();
        $activeProperties = (clone $properties)
            ->where('i.estado_actividad', 'Activo')
            ->countAllResults();

        $associations = $this->scopeApprovedOwners(
            $this->db->table('inmueble_servicios isv')
                ->join('inmuebles i', 'i.id = isv.inmueble_id')
                ->join('usuarios u', 'u.id = i.usuario_id')
        );
        $associationCount = (clone $associations)->countAllResults();
        $activeAssociations = (clone $associations)
            ->where('isv.estado !=', 'Inactivo')
            ->where('i.estado_actividad !=', 'Inactivo')
            ->countAllResults();

        $invoices = $this->scopeApprovedOwners(
            $this->db->table('facturas f')
                ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
                ->join('inmuebles i', 'i.id = isv.inmueble_id')
                ->join('usuarios u', 'u.id = i.usuario_id')
        );
        $pendingCount = (clone $invoices)
            ->where('f.estado_factura', 'Pendiente')
            ->countAllResults();
        $paidCount = (clone $invoices)
            ->where('f.estado_factura', 'Pagada')
            ->countAllResults();

        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $overdueCount = (clone $invoices)
            ->where('f.estado_factura', 'Pendiente')
            ->where(FacturaModel::FECHA_VENCIMIENTO_EFECTIVA . ' <', $today, false)
            ->countAllResults();

        return [
            'owners' => $ownerCount,
            'properties_active' => $activeProperties,
            'properties_inactive' => $propertyCount - $activeProperties,
            'associations_active' => $activeAssociations,
            'associations_inactive' => $associationCount - $activeAssociations,
            'invoices_pending' => $pendingCount,
            'invoices_paid' => $paidCount,
            'invoices_overdue' => $overdueCount,
            'overdue_percentage' => $pendingCount === 0
                ? null
                : ($overdueCount / $pendingCount) * 100,
        ];
    }

    /**
     * @return list<int>
     */
    public function findAvailableChartYears(): array
    {
        $years = [];
        $this->appendDistinctYears(
            $years,
            $this->scopeApprovedOwners($this->db->table('usuarios u')),
            'u.created_at'
        );
        $this->appendDistinctYears(
            $years,
            $this->scopeApprovedOwners(
                $this->db->table('inmuebles i')->join('usuarios u', 'u.id = i.usuario_id')
            ),
            'i.created_at'
        );
        $this->appendDistinctYears(
            $years,
            $this->scopeApprovedOwners(
                $this->db->table('inmueble_servicios isv')
                    ->join('inmuebles i', 'i.id = isv.inmueble_id')
                    ->join('usuarios u', 'u.id = i.usuario_id')
            ),
            'isv.created_at'
        );
        $this->appendDistinctYears($years, $this->db->table('servicios s'), 's.created_at');

        $years = array_values(array_unique($years));
        rsort($years, SORT_NUMERIC);

        return $years === [] ? [(int) date('Y')] : $years;
    }

    /**
     * @return array{year: int, metric: string, values: list<int>}
     */
    public function findMonthlyChartData(int $year, string $metric): array
    {
        $chartSources = [
            self::CHART_PROPERTIES => [
                $this->scopeApprovedOwners(
                    $this->db->table('inmuebles i')->join('usuarios u', 'u.id = i.usuario_id')
                ),
                'i.created_at',
            ],
            self::CHART_OWNERS => [
                $this->scopeApprovedOwners($this->db->table('usuarios u')),
                'u.created_at',
            ],
            self::CHART_ASSOCIATIONS => [
                $this->scopeApprovedOwners(
                    $this->db->table('inmueble_servicios isv')
                        ->join('inmuebles i', 'i.id = isv.inmueble_id')
                        ->join('usuarios u', 'u.id = i.usuario_id')
                ),
                'isv.created_at',
            ],
            self::CHART_SERVICE_TYPES => [
                $this->db->table('servicios s'),
                's.created_at',
            ],
        ];

        if (! isset($chartSources[$metric])) {
            throw new InvalidArgumentException('La métrica solicitada no es válida.');
        }

        $start = sprintf('%04d-01-01', $year);
        $end = sprintf('%04d-01-01', $year + 1);
        [$query, $createdAtField] = $chartSources[$metric];
        $rows = $query
            ->select($createdAtField)
            ->where($createdAtField . ' >=', $start)
            ->where($createdAtField . ' <', $end)
            ->get()
            ->getResultArray();

        $values = array_fill(0, 12, 0);
        foreach ($rows as $row) {
            $month = (int) substr((string) $row['created_at'], 5, 2);
            if ($month >= 1 && $month <= 12) {
                $values[$month - 1]++;
            }
        }

        return [
            'year' => $year,
            'metric' => $metric,
            'values' => $values,
        ];
    }

    /**
     * @return array{
     *     items: list<array<string, mixed>>,
     *     total: int,
     *     page: int,
     *     page_count: int
     * }
     */
    public function findOverdueInvoiceDetails(int $page = 1, int $perPage = 25): array
    {
        $today = new DateTimeImmutable('today');
        $todayString = $today->format('Y-m-d');
        $effectiveDueDate = FacturaModel::FECHA_VENCIMIENTO_EFECTIVA;
        $query = $this->scopeApprovedOwners(
            $this->db->table('facturas f')
                ->select([
                    'f.id AS factura_id',
                    'f.periodo_facturacion',
                    'f.monto_total',
                    'i.alias_nombre',
                    's.nombre AS servicio_nombre',
                    's.proveedor',
                    'u.nombre AS propietario_nombre',
                    'u.apellido AS propietario_apellido',
                ])
                ->select($effectiveDueDate . ' AS fecha_vencimiento_efectivo', false)
                ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
                ->join('inmuebles i', 'i.id = isv.inmueble_id')
                ->join('servicios s', 's.id = isv.servicio_id')
                ->join('usuarios u', 'u.id = i.usuario_id')
        )
            ->where('f.estado_factura', 'Pendiente')
            ->where($effectiveDueDate . ' <', $todayString, false);

        $total = (clone $query)->countAllResults();
        $perPage = max(1, $perPage);
        $pageCount = (int) ceil($total / $perPage);
        $page = min(max(1, $page), max(1, $pageCount));

        $items = (clone $query)
            ->orderBy($effectiveDueDate, 'ASC', false)
            ->orderBy('f.id', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        foreach ($items as &$invoice) {
            $invoice['dias_mora'] = (int) (new DateTimeImmutable($invoice['fecha_vencimiento_efectivo']))
                ->diff($today)
                ->format('%a');
        }
        unset($invoice);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'page_count' => $pageCount,
        ];
    }

    private function scopeApprovedOwners(BaseBuilder $query): BaseBuilder
    {
        return $query
            ->join('rol_usuario ru', 'ru.usuario_id = u.id')
            ->join('roles r', 'r.id = ru.rol_id')
            ->where('r.nombre', RolModel::ROLE_OWNER)
            ->where('u.estado_solicitud', UserModel::REQUEST_APPROVED);
    }

    /**
     * @param list<int> $years
     */
    private function appendDistinctYears(array &$years, BaseBuilder $query, string $createdAtField): void
    {
        $yearExpression = $this->db->DBDriver === 'SQLite3'
            ? "CAST(strftime('%Y', {$createdAtField}) AS INTEGER)"
            : "YEAR({$createdAtField})";
        $rows = $query
            ->select($yearExpression . ' AS year', false)
            ->distinct()
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $year = (int) $row['year'];
            if ($year > 0) {
                $years[] = $year;
            }
        }
    }
}

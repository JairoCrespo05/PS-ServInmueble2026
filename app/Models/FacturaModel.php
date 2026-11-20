<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTimeImmutable;

class FacturaModel extends Model
{
    protected $table         = 'facturas';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'inmueble_servicio_id',
        'periodo_facturacion',
        'fecha_emision',
        'fecha_primer_vencimiento',
        'fecha_segundo_vencimiento',
        'monto_total',
        'consumo_periodo',
        'lectura_anterior',
        'lectura_actual',
        'estado_factura',
        'fecha_pago_realizado',
        'numero_comprobante',
        'medio_pago',
        'monto_abonado',
        'mora',
    ];
    protected $useTimestamps = true;

    /**
     * @return array<int, int>
     */
    public function countPendingByPropertyForOwner(int $ownerId): array
    {
        $rows = $this->db->table('facturas f')
            ->select('i.id AS inmueble_id, COUNT(f.id) AS pendientes')
            ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->where('i.usuario_id', $ownerId)
            ->where('f.estado_factura', 'Pendiente')
            ->groupBy('i.id')
            ->get()
            ->getResultArray();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['inmueble_id']] = (int) $row['pendientes'];
        }

        return $counts;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findRecentForOwner(int $ownerId, int $limit = 5): array
    {
        return $this->db->table('facturas f')
            ->select([
                'f.id AS factura_id',
                'f.periodo_facturacion',
                'f.fecha_primer_vencimiento',
                'f.monto_total',
                'f.consumo_periodo',
                'f.estado_factura',
                'i.id AS inmueble_id',
                'i.alias_nombre',
                's.nombre AS servicio_nombre',
                's.proveedor',
                's.unidad_medida_std',
            ])
            ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->join('servicios s', 's.id = isv.servicio_id')
            ->where('i.usuario_id', $ownerId)
            ->orderBy('f.periodo_facturacion', 'DESC')
            ->orderBy('f.id', 'DESC')
            ->limit(max(1, $limit))
            ->get()
            ->getResultArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findNearestUpcomingForOwner(int $ownerId, int $withinDays = 7): ?array
    {
        $today = new DateTimeImmutable('today');
        $todayString = $today->format('Y-m-d');
        $lastEligibleDate = $today->modify('+' . max(0, $withinDays) . ' days')->format('Y-m-d');
        $invoices = $this->db->table('facturas f')
            ->select([
                'f.id AS factura_id',
                'f.fecha_primer_vencimiento',
                'f.fecha_segundo_vencimiento',
                'f.monto_total',
                'i.alias_nombre',
                's.nombre AS servicio_nombre',
                's.proveedor',
            ])
            ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->join('servicios s', 's.id = isv.servicio_id')
            ->where('i.usuario_id', $ownerId)
            ->where('f.estado_factura', 'Pendiente')
            ->groupStart()
                ->groupStart()
                    ->where('f.fecha_primer_vencimiento >=', $todayString)
                    ->where('f.fecha_primer_vencimiento <=', $lastEligibleDate)
                ->groupEnd()
                ->orGroupStart()
                    ->where('f.fecha_segundo_vencimiento >=', $todayString)
                    ->where('f.fecha_segundo_vencimiento <=', $lastEligibleDate)
                ->groupEnd()
            ->groupEnd()
            ->orderBy('f.id', 'DESC')
            ->get()
            ->getResultArray();

        $nearestInvoice = null;
        foreach ($invoices as $invoice) {
            $eligibleDates = array_filter(
                [$invoice['fecha_primer_vencimiento'], $invoice['fecha_segundo_vencimiento']],
                static fn ($date): bool => $date !== null && $date >= $todayString && $date <= $lastEligibleDate
            );

            if ($eligibleDates === []) {
                continue;
            }

            $nextDueDate = min($eligibleDates);
            if ($nearestInvoice !== null && $nextDueDate >= $nearestInvoice['proximo_vencimiento']) {
                continue;
            }

            $invoice['proximo_vencimiento'] = $nextDueDate;
            $invoice['dias_restantes'] = (int) $today->diff(new DateTimeImmutable($nextDueDate))->format('%a');
            $nearestInvoice = $invoice;
        }

        return $nearestInvoice;
    }
}

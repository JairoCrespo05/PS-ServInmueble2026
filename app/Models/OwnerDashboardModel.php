<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTimeImmutable;

class OwnerDashboardModel extends Model
{
    /**
     * @return array{
     *     property_count: int,
     *     active_service_count: int,
     *     pending_invoice_count: int,
     *     pending_invoice_amount: float,
     *     overdue_invoice_count: int,
     *     overdue_invoice_amount: float,
     *     paid_invoice_count: int,
     *     invoice_count: int
     * }
     */
    public function findMetricsForOwner(int $ownerId): array
    {
        $monthStart = new DateTimeImmutable('first day of this month');
        $monthStartString = $monthStart->format('Y-m-d');
        $monthEndString = $monthStart->modify('last day of this month')->format('Y-m-d');

        $propertyCount = $this->db->table('inmuebles')
            ->where('usuario_id', $ownerId)
            ->where('estado_actividad', 'Activo')
            ->countAllResults();

        $activeServiceCount = $this->db->table('inmueble_servicios isv')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->where('i.usuario_id', $ownerId)
            ->where('i.estado_actividad', 'Activo')
            ->where('isv.estado', 'Activo')
            ->where('isv.estado_vinculacion', 'Activo')
            ->countAllResults();

        $pendingInvoices = $this->db->table('facturas f')
            ->select('COUNT(f.id) AS invoice_count, COALESCE(SUM(f.monto_total), 0) AS invoice_amount')
            ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->where('i.usuario_id', $ownerId)
            ->where('f.estado_factura', 'Pendiente')
            ->groupStart()
                ->groupStart()
                    ->where('f.fecha_primer_vencimiento >=', $monthStartString)
                    ->where('f.fecha_primer_vencimiento <=', $monthEndString)
                ->groupEnd()
                ->orGroupStart()
                    ->where('f.fecha_segundo_vencimiento >=', $monthStartString)
                    ->where('f.fecha_segundo_vencimiento <=', $monthEndString)
                ->groupEnd()
            ->groupEnd()
            ->get()
            ->getRowArray();

        $todayString = (new DateTimeImmutable('today'))->format('Y-m-d');
        $overdueInvoices = $this->db->table('facturas f')
            ->select('COUNT(f.id) AS invoice_count, COALESCE(SUM(f.monto_total), 0) AS invoice_amount')
            ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->where('i.usuario_id', $ownerId)
            ->where('f.estado_factura', 'Pendiente')
            ->where(FacturaModel::FECHA_VENCIMIENTO_EFECTIVA . ' <', $todayString)
            ->get()
            ->getRowArray();

        $monthlyInvoices = $this->db->table('facturas f')
            ->select([
                'COUNT(f.id) AS invoice_count',
                "SUM(CASE WHEN f.estado_factura = 'Pagada' THEN 1 ELSE 0 END) AS paid_invoice_count",
            ])
            ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->where('i.usuario_id', $ownerId)
            ->where('f.periodo_facturacion >=', $monthStartString)
            ->where('f.periodo_facturacion <=', $monthEndString)
            ->get()
            ->getRowArray();

        return [
            'property_count' => (int) $propertyCount,
            'active_service_count' => (int) $activeServiceCount,
            'pending_invoice_count' => (int) ($pendingInvoices['invoice_count'] ?? 0),
            'pending_invoice_amount' => (float) ($pendingInvoices['invoice_amount'] ?? 0),
            'overdue_invoice_count' => (int) ($overdueInvoices['invoice_count'] ?? 0),
            'overdue_invoice_amount' => (float) ($overdueInvoices['invoice_amount'] ?? 0),
            'paid_invoice_count' => (int) ($monthlyInvoices['paid_invoice_count'] ?? 0),
            'invoice_count' => (int) ($monthlyInvoices['invoice_count'] ?? 0),
        ];
    }
}

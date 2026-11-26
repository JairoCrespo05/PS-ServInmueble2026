<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTimeImmutable;

class FacturaModel extends Model
{
    public const FECHA_VENCIMIENTO_EFECTIVA = 'COALESCE(f.fecha_segundo_vencimiento, f.fecha_primer_vencimiento)';

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
        $invoices = $this->db->table('facturas f')
            ->select([
                'f.id AS factura_id',
                'f.periodo_facturacion',
                'f.fecha_primer_vencimiento',
                'f.fecha_segundo_vencimiento',
                'f.monto_total',
                'f.consumo_periodo',
                'f.estado_factura',
                'i.id AS inmueble_id',
                'i.alias_nombre',
                's.nombre AS servicio_nombre',
                's.proveedor',
                's.unidad_medida_std',
            ])
            ->select(self::FECHA_VENCIMIENTO_EFECTIVA . ' AS fecha_vencimiento_efectivo', false)
            ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->join('servicios s', 's.id = isv.servicio_id')
            ->where('i.usuario_id', $ownerId)
            ->orderBy('f.periodo_facturacion', 'DESC')
            ->orderBy('f.id', 'DESC')
            ->limit(max(1, $limit))
            ->get()
            ->getResultArray();

        $today = new DateTimeImmutable('today');
        foreach ($invoices as &$invoice) {
            $isOverdue = $invoice['estado_factura'] === 'Pendiente'
                && $invoice['fecha_vencimiento_efectivo'] < $today->format('Y-m-d');
            $invoice['estado_visual'] = $isOverdue ? 'Vencida' : $invoice['estado_factura'];
            $invoice['dias_mora'] = $isOverdue
                ? (int) (new DateTimeImmutable($invoice['fecha_vencimiento_efectivo']))->diff($today)->format('%a')
                : 0;
        }
        unset($invoice);

        return $invoices;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findOverdueForOwner(int $ownerId, int $limit = 5): array
    {
        $today = new DateTimeImmutable('today');
        $todayString = $today->format('Y-m-d');

        $invoices = $this->db->table('facturas f')
            ->select([
                'f.id AS factura_id',
                'f.periodo_facturacion',
                'f.monto_total',
                'i.alias_nombre',
                's.nombre AS servicio_nombre',
                's.proveedor',
            ])
            ->select(self::FECHA_VENCIMIENTO_EFECTIVA . ' AS fecha_vencimiento_efectivo', false)
            ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->join('servicios s', 's.id = isv.servicio_id')
            ->where('i.usuario_id', $ownerId)
            ->where('f.estado_factura', 'Pendiente')
            ->where(self::FECHA_VENCIMIENTO_EFECTIVA . ' <', $todayString)
            ->orderBy(self::FECHA_VENCIMIENTO_EFECTIVA, 'ASC', false)
            ->orderBy('f.id', 'DESC')
            ->limit(max(1, $limit))
            ->get()
            ->getResultArray();

        foreach ($invoices as &$invoice) {
            $invoice['dias_mora'] = (int) (new DateTimeImmutable($invoice['fecha_vencimiento_efectivo']))
                ->diff($today)
                ->format('%a');
        }
        unset($invoice);

        return $invoices;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findNearestUpcomingForOwner(int $ownerId, int $withinDays = 7): ?array
    {
        $today = new DateTimeImmutable('today');
        $todayString = $today->format('Y-m-d');
        $lastEligibleDate = $today->modify('+' . max(0, $withinDays) . ' days')->format('Y-m-d');
        $effectiveDueDate = self::FECHA_VENCIMIENTO_EFECTIVA;
        $invoice = $this->db->table('facturas f')
            ->select([
                'f.id AS factura_id',
                'f.fecha_primer_vencimiento',
                'f.fecha_segundo_vencimiento',
                'f.monto_total',
                'i.alias_nombre',
                's.nombre AS servicio_nombre',
                's.proveedor',
            ])
            ->select($effectiveDueDate . ' AS proximo_vencimiento', false)
            ->join('inmueble_servicios isv', 'isv.id = f.inmueble_servicio_id')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->join('servicios s', 's.id = isv.servicio_id')
            ->where('i.usuario_id', $ownerId)
            ->where('f.estado_factura', 'Pendiente')
            ->where($effectiveDueDate . ' >=', $todayString)
            ->where($effectiveDueDate . ' <=', $lastEligibleDate)
            ->orderBy($effectiveDueDate, 'ASC', false)
            ->orderBy('f.id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($invoice === null) {
            return null;
        }

        $invoice['dias_restantes'] = (int) $today->diff(new DateTimeImmutable($invoice['proximo_vencimiento']))->format('%a');

        return $invoice;
    }
}

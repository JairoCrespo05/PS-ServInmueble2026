<?php

namespace App\Models;

use CodeIgniter\Model;

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
}

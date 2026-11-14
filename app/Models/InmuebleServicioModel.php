<?php

namespace App\Models;

use CodeIgniter\Model;

class InmuebleServicioModel extends Model
{
    protected $table         = 'inmueble_servicios';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'inmueble_id',
        'servicio_id',
        'nro_medidor',
        'titular_inmueble',
        'estado',
        'nro_cuenta_contrato',
        'cod_pago_electronico',
        'domicilio_facturacion',
    ];
    protected $useTimestamps = true;
}

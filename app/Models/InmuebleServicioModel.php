<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\BaseBuilder;

class InmuebleServicioModel extends Model
{
    public const ESTADOS_OPERATIVOS = ['Activo', 'Pausado', 'En Revisión'];

    protected $table         = 'inmueble_servicios';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'inmueble_id',
        'servicio_id',
        'nro_medidor',
        'titular_inmueble',
        'estado',
        'estado_vinculacion',
        'nro_cuenta_contrato',
        'cod_pago_electronico',
        'domicilio_facturacion',
    ];
    protected $useTimestamps = true;

    public function facturablesParaPropietario(int $ownerId): BaseBuilder
    {
        return $this->db->table('inmueble_servicios isv')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->where('i.usuario_id', $ownerId)
            ->where('i.estado_actividad', 'Activo')
            ->where("LOWER(TRIM(isv.estado)) != 'inactivo'", null, false)
            ->where('isv.estado_vinculacion', 'Activo');
    }
}

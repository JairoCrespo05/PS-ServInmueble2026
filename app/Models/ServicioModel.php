<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table         = 'servicios';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nombre', 'unidad_medida_std', 'proveedor', 'categoria'];
    protected $useTimestamps = true;
}

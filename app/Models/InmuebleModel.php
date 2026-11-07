<?php

namespace App\Models;

use CodeIgniter\Model;

class InmuebleModel extends Model
{
    protected $table         = 'inmuebles';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['alias_nombre', 'tipo', 'superficie', 'estado', 'direccion_id'];
    protected $useTimestamps = true;
}

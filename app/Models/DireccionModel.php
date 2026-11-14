<?php

namespace App\Models;

use CodeIgniter\Model;

class DireccionModel extends Model
{
    protected $table         = 'direcciones';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['calle', 'numero', 'piso_dpto', 'ciudad'];
    protected $useTimestamps = true;
}

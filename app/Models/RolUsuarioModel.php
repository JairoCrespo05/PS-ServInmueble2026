<?php 

namespace App\Models;

use CodeIgniter\Model;

class RolUsuarioModel extends Model
{
    protected $table         = 'rol_usuario';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['rol_id', 'usuario_id'];
    protected $useTimestamps = false;
}

<?php 

namespace App\Models;

use CodeIgniter\Model;

class RolModel extends Model
{
    public const ROLE_ADMINISTRATOR = 'Administrador';
    public const ROLE_OWNER         = 'Propietario';
    public const ROLE_PENDING       = 'Usuario_Nuevo';

    protected $table         = 'roles';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nombre'];
    protected $useTimestamps = false;

    public function getIdByName(string $name): ?int
    {
        $role = $this->where('nombre', $name)->first();

        return $role === null ? null : (int) $role['id'];
    }
}

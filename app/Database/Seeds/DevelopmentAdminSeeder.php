<?php

namespace App\Database\Seeds;

use App\Models\RolModel;
use CodeIgniter\Database\Seeder;
use RuntimeException;

class DevelopmentAdminSeeder extends Seeder
{
    private const ADMIN_EMAIL = 'admin.pruebas@example.com';
    private const ADMIN_PASSWORD = 'Passw0rd!';

    public function run(): void
    {
        $environmentOverride = getenv('CI_ENVIRONMENT');
        if (ENVIRONMENT !== 'development'
            || ($environmentOverride !== false && $environmentOverride !== 'development')) {
            throw new RuntimeException('El administrador de prueba solo se puede crear en development.');
        }

        $role = $this->db->table('roles')
            ->select('id')
            ->where('nombre', RolModel::ROLE_ADMINISTRATOR)
            ->get()
            ->getRowArray();

        if ($role === null) {
            throw new RuntimeException('No existe el rol Administrador. Ejecutá primero las migraciones.');
        }

        $existingUser = $this->db->table('usuarios')
            ->select('id')
            ->where('email', self::ADMIN_EMAIL)
            ->get()
            ->getRowArray();

        if ($existingUser !== null) {
            $hasAdministratorRole = $this->db->table('rol_usuario')
                ->where('usuario_id', $existingUser['id'])
                ->where('rol_id', $role['id'])
                ->countAllResults() > 0;

            if ($hasAdministratorRole) {
                return;
            }

            throw new RuntimeException('El correo reservado para el admin de prueba ya existe con otro rol.');
        }

        if (! $this->db->transBegin()) {
            throw new RuntimeException('No se pudo iniciar la transacción para crear el admin de prueba.');
        }

        $now = date('Y-m-d H:i:s');
        $userInserted = $this->db->table('usuarios')->insert([
            'nombre'     => 'Admin',
            'apellido'   => 'Pruebas',
            'email'      => self::ADMIN_EMAIL,
            'password'   => password_hash(self::ADMIN_PASSWORD, PASSWORD_DEFAULT),
            'telefono'   => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $userId = (int) $this->db->insertID();
        $roleInserted = $userInserted && $userId > 0 && $this->db->table('rol_usuario')->insert([
            'usuario_id' => $userId,
            'rol_id'     => $role['id'],
        ]);

        if (! $roleInserted || $this->db->transStatus() === false) {
            $this->db->transRollback();

            throw new RuntimeException('No se pudo asignar el rol Administrador al usuario de prueba.');
        }

        if (! $this->db->transCommit()) {
            $this->db->transRollback();

            throw new RuntimeException('No se pudo confirmar la creación del admin de prueba.');
        }
    }
}

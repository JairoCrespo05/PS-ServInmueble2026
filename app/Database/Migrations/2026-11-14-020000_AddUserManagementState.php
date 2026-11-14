<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserManagementState extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('usuarios', [
            'estado_solicitud' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'Aprobada',
                'null'       => false,
            ],
            'activo' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
                'null'       => false,
            ],
        ]);

        $existingUsers = $this->db->table('usuarios u')
            ->select('u.id, r.nombre AS role')
            ->join('rol_usuario ru', 'ru.usuario_id = u.id')
            ->join('roles r', 'r.id = ru.rol_id')
            ->get()
            ->getResultArray();

        foreach ($existingUsers as $user) {
            $requestStatus = $user['role'] === 'Usuario_Nuevo' ? 'Pendiente' : 'Aprobada';
            $this->db->table('usuarios')
                ->where('id', $user['id'])
                ->update(['estado_solicitud' => $requestStatus]);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('usuarios', 'estado_solicitud');
        $this->forge->dropColumn('usuarios', 'activo');
    }
}

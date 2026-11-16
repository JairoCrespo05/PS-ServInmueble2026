<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOwnerIdToExistingProperties extends Migration
{
    public function up(): void
    {
        if ($this->db->fieldExists('usuario_id', 'inmuebles')) {
            return;
        }

        $this->forge->addColumn('inmuebles', [
            'usuario_id' => [
                'type' => 'INT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('usuario_id');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->processIndexes('inmuebles');
    }

    public function down(): void
    {
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddProvinciaToDirecciones extends Migration
{
    public function up(): void
    {
        if ($this->db->fieldExists('provincia', 'direcciones')) {
            return;
        }

        if (! $this->forge->addColumn('direcciones', [
            'provincia' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ])) {
            throw new RuntimeException('No se pudo agregar la provincia a las direcciones.');
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('provincia', 'direcciones')) {
            $this->forge->dropColumn('direcciones', 'provincia');
        }
    }
}

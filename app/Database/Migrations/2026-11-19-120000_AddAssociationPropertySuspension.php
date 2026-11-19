<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddAssociationPropertySuspension extends Migration
{
    public function up(): void
    {
        if ($this->db->fieldExists('motivo_suspension', 'inmueble_servicios')) {
            $this->suspendLinksOfInactiveProperties();
            return;
        }

        if (! $this->forge->addColumn('inmueble_servicios', [
            'motivo_suspension' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
        ])) {
            throw new RuntimeException('No se pudo registrar el motivo de suspensión de los vínculos.');
        }

        $this->suspendLinksOfInactiveProperties();
    }

    private function suspendLinksOfInactiveProperties(): void
    {
        $rows = $this->db->table('inmueble_servicios isv')
            ->select('isv.id')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->where('i.estado_actividad', 'Inactivo')
            ->where("LOWER(TRIM(isv.estado)) != 'inactivo'", null, false)
            ->get()
            ->getResultArray();

        $this->db->transStart();
        foreach ($rows as $row) {
            $this->db->table('inmueble_servicios')
                ->where('id', (int) $row['id'])
                ->update([
                    'estado' => 'Inactivo',
                    'motivo_suspension' => 'BajaInmueble',
                ]);
        }
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('No se pudieron suspender los vínculos de inmuebles dados de baja.');
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('motivo_suspension', 'inmueble_servicios')) {
            $this->forge->dropColumn('inmueble_servicios', 'motivo_suspension');
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddEstadoVinculacion extends Migration
{
    public function up(): void
    {
        $hasColumn = $this->db->fieldExists('estado_vinculacion', 'inmueble_servicios');
        $fields = $hasColumn ? 'estado, estado_vinculacion' : 'estado';

        foreach ($this->db->table('inmueble_servicios')->select($fields)->get()->getResultArray() as $row) {
            if (! in_array(mb_strtolower(trim($row['estado'])), ['activo', 'inactivo', 'suspendido'], true)) {
                throw new RuntimeException('Hay vínculos con estados anteriores no reconocidos; revisalos antes de migrar.');
            }

            $operational = $row['estado_vinculacion'] ?? null;
            if ($operational !== null && trim($operational) !== '' && ! in_array($operational, ['Activo', 'Pausado', 'En Revisión'], true)) {
                throw new RuntimeException('Hay estados operativos no reconocidos; revisalos antes de migrar.');
            }
        }

        if (! $hasColumn && ! $this->forge->addColumn('inmueble_servicios', [
            'estado_vinculacion' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        ])) {
            throw new RuntimeException('No se pudo agregar el estado operativo del vínculo.');
        }

        $this->db->transStart();
        foreach (['activo' => 'Activo', 'suspendido' => 'Pausado'] as $previous => $operational) {
            $this->db->table('inmueble_servicios')
                ->where('LOWER(TRIM(estado))', $previous)
                ->groupStart()
                    ->where('estado_vinculacion', null)
                    ->orWhere('TRIM(estado_vinculacion)', '')
                ->groupEnd()
                ->update(['estado_vinculacion' => $operational]);
        }
        $this->db->table('inmueble_servicios')
            ->where('LOWER(TRIM(estado))', 'suspendido')
            ->update(['estado' => 'Activo']);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('No se pudieron migrar los estados de los vínculos.');
        }
    }

    public function down(): void
    {
        throw new RuntimeException('La reversión automática no es segura: el estado operativo puede contener datos previos o posteriores a la migración.');
    }
}

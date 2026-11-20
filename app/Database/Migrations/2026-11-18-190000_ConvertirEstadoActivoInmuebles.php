<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class ConvertirEstadoActivoInmuebles extends Migration
{
    public function up(): void
    {
        $hasActivity = $this->db->fieldExists('estado_actividad', 'inmuebles');
        $rows = $this->db->table('inmuebles')->select($hasActivity ? 'estado, estado_actividad' : 'estado')->distinct()->get()->getResultArray();
        foreach ($rows as $row) {
            $state = $row['estado'] === null ? null : mb_strtolower(trim($row['estado']));
            $activity = $row['estado_actividad'] ?? null;
            if ($state === null || ! in_array($state, ['activo', 'disponible', 'ocupado', 'en mantenimiento', 'inactivo', 'desconocido'], true)) {
                throw new RuntimeException('Hay inmuebles con estados no reconocidos; revisalos antes de migrar.');
            }
            if ($activity !== null && ! in_array($activity, ['Activo', 'Inactivo'], true)) {
                throw new RuntimeException('Hay estados de actividad no reconocidos; revisalos antes de migrar.');
            }
            if (($state === 'inactivo' && $activity === 'Activo') || ($state === 'desconocido' && $activity !== 'Inactivo')) {
                throw new RuntimeException('Hay inmuebles con estado y actividad incompatibles; revisalos antes de migrar.');
            }
        }

        if (! $hasActivity && ! $this->forge->addColumn('inmuebles', [
            'estado_actividad' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
        ])) {
            throw new RuntimeException('No se pudo agregar el estado de actividad del inmueble.');
        }
        $this->db->resetDataCache();

        $this->db->transStart();
        $this->db->table('inmuebles')->where('LOWER(TRIM(estado))', 'inactivo')->where('estado_actividad', null)
            ->update(['estado_actividad' => 'Inactivo']);
        $this->db->table('inmuebles')->where('estado_actividad', null)->update(['estado_actividad' => 'Activo']);
        $this->db->table('inmuebles')->where('LOWER(TRIM(estado))', 'inactivo')->update(['estado' => 'Desconocido']);
        $this->db->table('inmuebles')->where('LOWER(TRIM(estado))', 'activo')->update(['estado' => 'Disponible']);
        $this->db->transComplete();
        if (! $this->db->transStatus()) {
            throw new RuntimeException('No se pudieron convertir los estados de los inmuebles.');
        }
        if ($this->db->DBDriver !== 'SQLite3' && ! $this->forge->modifyColumn('inmuebles', [
            'estado_actividad' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false, 'default' => 'Activo'],
        ])) {
            throw new RuntimeException('No se pudo completar la definición del estado de actividad.');
        }
    }

    public function down(): void
    {
        throw new RuntimeException('La reversión automática no es segura: no se distingue qué inmuebles ya estaban disponibles.');
    }
}

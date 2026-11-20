<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddInmuebleOwnerLink extends Migration
{
    public function up(): void
    {
        if ($this->db->DBDriver === 'MySQLi') {
            $usersTable = $this->db->DBPrefix . 'usuarios';
            $usersEngine = $this->db->query(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                [$usersTable]
            )->getRowArray();

            if ($usersEngine === null) {
                throw new RuntimeException('No se encontró la tabla de usuarios para vincular inmuebles.');
            }

            if (strcasecmp((string) $usersEngine['ENGINE'], 'InnoDB') !== 0) {
                $this->db->query(
                    'ALTER TABLE ' . $this->db->escapeIdentifiers($usersTable) . ' ENGINE=InnoDB'
                );
            }
        }

        if (! $this->db->fieldExists('usuario_id', 'inmuebles')) {
            if (! $this->forge->addColumn('inmuebles', [
                'usuario_id' => ['type' => 'INT', 'null' => true],
            ])) {
                throw new RuntimeException('No se pudo agregar el propietario al esquema de inmuebles.');
            }

            $this->forge->addKey('usuario_id', false, false, 'inmuebles_usuario_id_idx');
            $this->forge->addForeignKey(
                'usuario_id',
                'usuarios',
                'id',
                'CASCADE',
                'RESTRICT',
                'inmuebles_usuario_id_fk'
            );

            if (! $this->forge->processIndexes('inmuebles')) {
                throw new RuntimeException('No se pudo crear la relación de propietario para los inmuebles.');
            }

            return;
        }

        if ($this->db->DBDriver === 'MySQLi') {
            $propertiesTable = $this->db->DBPrefix . 'inmuebles';
            $column = $this->db->query(
                'SHOW COLUMNS FROM ' . $this->db->escapeIdentifiers($propertiesTable) . " LIKE 'usuario_id'"
            )->getRowArray();

            if ($column === null) {
                throw new RuntimeException('No se pudo inspeccionar la columna de propietario de los inmuebles.');
            }

            if ($column['Null'] === 'NO' && ! $this->forge->modifyColumn('inmuebles', [
                'usuario_id' => ['type' => 'INT', 'null' => true],
            ])) {
                throw new RuntimeException('No se pudo permitir que un inmueble quede sin propietario asignado.');
            }

            $foreignKey = $this->db->query(
                'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE'
                . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
                . ' AND REFERENCED_TABLE_NAME = ? AND REFERENCED_COLUMN_NAME = ?',
                [$propertiesTable, 'usuario_id', $usersTable, 'id']
            )->getRowArray();

            if ($foreignKey === null) {
                $this->forge->addForeignKey(
                    'usuario_id',
                    'usuarios',
                    'id',
                    'CASCADE',
                    'RESTRICT',
                    'inmuebles_usuario_id_fk'
                );

                if (! $this->forge->processIndexes('inmuebles')) {
                    throw new RuntimeException('No se pudo crear la clave foránea del propietario del inmueble.');
                }
            }
        }
    }

    public function down(): void
    {
        // usuario_id belongs to the baseline inmuebles schema; removing it would discard ownership assignments.
    }
}

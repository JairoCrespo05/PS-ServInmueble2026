<?php

use App\Database\Migrations\AddEstadoVinculacion;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

require_once APPPATH . 'Database/Migrations/2026-11-18-180000_AddEstadoVinculacion.php';

/**
 * @internal
 */
final class AssociationStateMigrationTest extends CIUnitTestCase
{
    public function testExistingColumnAndHistoricalStatesArePreserved(): void
    {
        $db = db_connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $db->query('CREATE TABLE inmueble_servicios (id INTEGER PRIMARY KEY, estado TEXT NOT NULL, estado_vinculacion TEXT NULL)');
        $db->query("INSERT INTO inmueble_servicios VALUES (1, 'Activo', NULL), (2, 'Suspendido', NULL), (3, 'Inactivo', NULL), (4, 'Activo', 'Pausado'), (5, 'Suspendido', 'En Revisión')");

        (new AddEstadoVinculacion(Database::forge($db)))->up();

        $rows = $db->table('inmueble_servicios')->orderBy('id')->get()->getResultArray();
        $this->assertSame(['Activo', 'Pausado', null, 'Pausado', 'En Revisión'], array_column($rows, 'estado_vinculacion'));
        $this->assertSame(['Activo', 'Activo', 'Inactivo', 'Activo', 'Activo'], array_column($rows, 'estado'));
        $db->close();
    }

    public function testNewColumnIsCreatedForFreshInstallations(): void
    {
        $db = db_connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $db->query('CREATE TABLE inmueble_servicios (id INTEGER PRIMARY KEY, estado TEXT NOT NULL)');
        $db->query("INSERT INTO inmueble_servicios VALUES (1, 'Suspendido'), (2, 'Inactivo')");

        (new AddEstadoVinculacion(Database::forge($db)))->up();

        $rows = $db->table('inmueble_servicios')->orderBy('id')->get()->getResultArray();
        $this->assertSame('Pausado', $rows[0]['estado_vinculacion']);
        $this->assertSame('Activo', $rows[0]['estado']);
        $this->assertNull($rows[1]['estado_vinculacion']);
        $db->close();
    }

    public function testUnknownExistingOperationalStatusStopsMigration(): void
    {
        $db = db_connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $db->query('CREATE TABLE inmueble_servicios (id INTEGER PRIMARY KEY, estado TEXT NOT NULL, estado_vinculacion TEXT NULL)');
        $db->query("INSERT INTO inmueble_servicios VALUES (1, 'Suspendido', 'Otro')");

        try {
            (new AddEstadoVinculacion(Database::forge($db)))->up();
            $this->fail('La migración debe rechazar estados desconocidos.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('no reconocidos', $exception->getMessage());
        }

        $row = $db->table('inmueble_servicios')->get()->getRowArray();
        $this->assertSame('Suspendido', $row['estado']);
        $this->assertSame('Otro', $row['estado_vinculacion']);
        $db->close();
    }
}

<?php

use App\Database\Migrations\ConvertirEstadoActivoInmuebles;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

require_once APPPATH . 'Database/Migrations/2026-11-18-190000_ConvertirEstadoActivoInmuebles.php';

/**
 * @internal
 */
final class PropertyStateMigrationTest extends CIUnitTestCase
{
    public function testConvertsOnlyActivePropertiesAndPreservesLogicalDeletion(): void
    {
        $db = db_connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $db->query('CREATE TABLE inmuebles (id INTEGER PRIMARY KEY, estado TEXT NOT NULL)');
        $db->query("INSERT INTO inmuebles VALUES (1, 'Activo'), (2, ' activo '), (3, 'Disponible'), (4, 'Ocupado'), (5, 'En Mantenimiento'), (6, 'Inactivo')");
        $migration = new ConvertirEstadoActivoInmuebles(Database::forge($db));
        $migration->up();
        $migration->up();

        $rows = $db->table('inmuebles')->orderBy('id')->get()->getResultArray();
        $this->assertSame(['Disponible', 'Disponible', 'Disponible', 'Ocupado', 'En Mantenimiento', 'Desconocido'], array_column($rows, 'estado'));
        $this->assertSame(['Activo', 'Activo', 'Activo', 'Activo', 'Activo', 'Inactivo'], array_column($rows, 'estado_actividad'));
        $db->close();
    }

    public function testUnknownStateStopsConversionBeforeChangingData(): void
    {
        $db = db_connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $db->query('CREATE TABLE inmuebles (id INTEGER PRIMARY KEY, estado TEXT NOT NULL)');
        $db->query("INSERT INTO inmuebles VALUES (1, 'Activo'), (2, 'Otro')");
        try {
            (new ConvertirEstadoActivoInmuebles(Database::forge($db)))->up();
            $this->fail('La migración debe rechazar estados desconocidos.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('no reconocidos', $exception->getMessage());
        }
        $this->assertSame('Activo', $db->table('inmuebles')->where('id', 1)->get()->getRow()->estado);
        $db->close();
    }

    public function testExistingActivityIsPreservedAndConflictsAreRejected(): void
    {
        $db = db_connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $db->query('CREATE TABLE inmuebles (id INTEGER PRIMARY KEY, estado TEXT NOT NULL, estado_actividad TEXT)');
        $db->query("INSERT INTO inmuebles VALUES (1, 'Disponible', 'Inactivo'), (2, 'Inactivo', 'Activo')");
        $migration = new ConvertirEstadoActivoInmuebles(Database::forge($db));
        try {
            $migration->up();
            $this->fail('La migración debe rechazar datos contradictorios.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('incompatibles', $exception->getMessage());
        }
        $db->table('inmuebles')->where('id', 2)->update(['estado_actividad' => 'Inactivo']);
        $migration->up();
        $rows = $db->table('inmuebles')->orderBy('id')->get()->getResultArray();
        $this->assertSame(['Disponible', 'Desconocido'], array_column($rows, 'estado'));
        $this->assertSame(['Inactivo', 'Inactivo'], array_column($rows, 'estado_actividad'));
        $db->close();
    }
}

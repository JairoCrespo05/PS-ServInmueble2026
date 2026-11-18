<?php

use App\Models\FacturaModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PropertyPendingInvoicesTest extends CIUnitTestCase
{
    public function testCountsAllPendingInvoicesOnlyForTheOwner(): void
    {
        $db = db_connect([
            'DBDriver' => 'SQLite3',
            'database' => ':memory:',
            'DBPrefix' => '',
            'DBDebug' => true,
        ], false);
        $db->query('CREATE TABLE inmuebles (id INTEGER PRIMARY KEY, usuario_id INTEGER)');
        $db->query('CREATE TABLE inmueble_servicios (id INTEGER PRIMARY KEY, inmueble_id INTEGER, estado TEXT)');
        $db->query('CREATE TABLE facturas (id INTEGER PRIMARY KEY, inmueble_servicio_id INTEGER, estado_factura TEXT, fecha_primer_vencimiento TEXT)');
        $db->query('INSERT INTO inmuebles VALUES (1, 10), (2, 10), (3, 20)');
        $db->query("INSERT INTO inmueble_servicios VALUES (1, 1, 'Activo'), (2, 1, 'Inactivo'), (3, 2, 'Activo'), (4, 3, 'Activo')");
        $db->query("INSERT INTO facturas VALUES (1, 1, 'Pendiente', '2000-01-01'), (2, 2, 'Pendiente', '2099-01-01'), (3, 1, 'Pagada', '2000-01-01'), (4, 3, 'Pagada', '2099-01-01'), (5, 4, 'Pendiente', '2000-01-01')");

        $model = new FacturaModel($db);
        $this->assertSame([1 => 2], $model->countPendingByPropertyForOwner(10));
        $this->assertSame([3 => 1], $model->countPendingByPropertyForOwner(20));
        $this->assertSame([], $model->countPendingByPropertyForOwner(30));
        $db->close();
    }

    public function testCardsExposeCityAndPendingInvoiceCount(): void
    {
        $property = [
            'id' => 1, 'alias_nombre' => 'Casa de prueba', 'tipo' => 'Casa', 'superficie' => 100,
            'estado' => 'Activo', 'direccion_id' => 1, 'calle' => 'Calle', 'numero' => '10',
            'piso_dpto' => null, 'provincia' => 'Córdoba', 'ciudad' => 'Córdoba',
            'inmueble_created_at' => null, 'inmueble_updated_at' => null,
            'direccion_created_at' => null, 'direccion_updated_at' => null,
            'servicios_activos' => 0, 'nombres_servicios_activos' => '',
        ];
        $withoutPending = $property;
        $withoutPending['id'] = 2;
        $withoutPending['alias_nombre'] = 'Otra casa';
        $html = view('inmuebles/index', [
            'inmuebles' => [$property, $withoutPending],
            'serviciosPorInmueble' => [],
            'facturasPendientesPorInmueble' => [1 => 2],
            'resumen' => ['total' => 1, 'activos' => 1, 'superficie_total' => 100, 'servicios_activos' => 0],
        ]);
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($document);
        $card = $xpath->query('//article[@data-property-row]')->item(0);
        $this->assertSame('córdoba', $card->getAttribute('data-city'));
        $this->assertSame('con', $card->getAttribute('data-debt'));
        $this->assertStringContainsString('2 facturas pendientes', $html);
        $this->assertSame('sin', $xpath->query('//article[@data-property-row]')->item(1)->getAttribute('data-debt'));
        $this->assertStringContainsString('Sin facturas pendientes', $html);
        $this->assertSame(1, $xpath->query('//select[@id="filter-city"]/option[@value="córdoba"]')->length);
        $this->assertStringContainsString('data-view-property', $html);
        $this->assertStringContainsString('data-edit-property', $html);
    }
}

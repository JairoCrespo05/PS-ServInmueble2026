<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RadioFormTest extends CIUnitTestCase
{
    public function testPropertyStatusRetainsSubmittedValue(): void
    {
        session()->setFlashdata('formData', ['estado' => 'Ocupado']);

        $xpath = $this->renderXPath('inmuebles/index', [
            'inmuebles' => [],
            'facturasPendientesPorInmueble' => [],
            'resumen' => ['total' => 0, 'activos' => 0, 'superficie_total' => 0, 'servicios_activos' => 0],
        ]);

        $this->assertSame(1, $xpath->query('//fieldset[legend="Estado"]//input[@type="radio" and @name="estado" and @value="Ocupado" and @checked]')->length);
        $this->assertSame(0, $xpath->query('//select[@name="estado"]')->length);
    }

    public function testAssociationStatusRetainsSubmittedValue(): void
    {
        $xpath = $this->renderXPath('servicios/index', [
            'servicios' => [],
            'catalogoServicios' => [],
            'inmuebles' => [],
            'formData' => ['estado' => 'Suspendido'],
            'errors' => [],
            'success' => null,
            'error' => null,
            'resumen' => ['total' => 0, 'asociaciones' => 0, 'inmuebles' => 0],
            'serviceLocked' => false,
        ]);

        $this->assertSame(1, $xpath->query('//fieldset[legend="Estado del servicio"]//input[@type="radio" and @name="estado" and @value="Suspendido" and @checked]')->length);
        $this->assertSame(0, $xpath->query('//select[@name="estado"]')->length);
    }

    public function testServiceCategoryPreservesCreateSelectionAndCustomEdit(): void
    {
        $data = [
            'servicios' => [],
            'unidadesPorCategoria' => ['Agua' => 'm3', 'Gas' => 'm3'],
            'formData' => ['categoria' => 'Agua'],
        ];
        $create = $this->renderXPath('servicios/tipos', $data);
        $this->assertSame(1, $create->query('//*[@id="create-category-field"]//input[@name="categoria" and @value="Agua" and @checked and @data-unit="m3"]')->length);
        $this->assertSame(1, $create->query('//*[@id="edit-category-field" and @disabled]')->length);

        $data['editId'] = 1;
        $data['formData']['categoria'] = 'Categoría personalizada';
        $edit = $this->renderXPath('servicios/tipos', $data);
        $this->assertSame(1, $edit->query('//*[@id="create-category-field" and @disabled]')->length);
        $this->assertSame(1, $edit->query('//*[@id="edit-service-category" and @name="categoria" and not(@disabled)]')->length);
        $this->assertSame('Categoría personalizada', $edit->query('//*[@id="edit-service-category"]')->item(0)->getAttribute('value'));
    }

    private function renderXPath(string $view, array $data): DOMXPath
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . view($view, $data));
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }
}

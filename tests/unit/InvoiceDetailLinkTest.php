<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class InvoiceDetailLinkTest extends CIUnitTestCase
{
    public function testInvoiceListAndReportShowAccessibleIconOnlyLinks(): void
    {
        $invoice = [
            'factura_id' => 12,
            'periodo_facturacion' => '2026-10-01',
            'alias_nombre' => 'Casa',
            'servicio_nombre' => 'Electricidad',
            'proveedor' => 'Proveedor',
            'monto_total' => '100.00',
            'fecha_primer_vencimiento' => '2026-10-20',
            'estado_factura' => 'Pendiente',
        ];
        $paid = $invoice;
        $paid['factura_id'] = 13;
        $paid['estado_factura'] = 'Pagada';
        $report = view('facturas/report', [
            'pendientes' => [$invoice],
            'pagadas' => [$paid],
            'cantidadPendiente' => 1,
            'cantidadPagada' => 1,
            'coincidenciasPendientes' => 1,
            'coincidenciasPagadas' => 1,
            'totalCoincidencias' => 2,
            'totalFacturas' => 2,
            'buscar' => '',
            'totalPendiente' => 100,
            'totalPagado' => 100,
        ]);

        $views = [
            [view('facturas/index', [
                'facturas' => [$invoice],
                'success' => null,
                'error' => null,
                'pagerLinks' => '',
                'totalFacturas' => 1,
                'totalResultados' => 1,
                'buscar' => '',
                'estado' => '',
            ]), [12]],
            [$report, [12, 13]],
        ];

        foreach ($views as [$html, $ids]) {
            $this->assertStringContainsString(
                base_url('css/facturas.css') . '?v=' . filemtime(FCPATH . 'css/facturas.css'),
                $html
            );
            $document = new DOMDocument();
            $previous = libxml_use_internal_errors(true);
            $document->loadHTML('<?xml encoding="UTF-8">' . $html);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            $links = (new DOMXPath($document))->query('//a[@class="invoice-detail-link"]');
            $this->assertCount(count($ids), $links);

            foreach ($ids as $index => $id) {
                $link = $links->item($index);
                $this->assertSame(site_url('facturas/' . $id), $link->getAttribute('href'));
                $this->assertSame('Ver detalle de Electricidad 10/2026', $link->getAttribute('aria-label'));
                $this->assertSame('visibility', trim($link->textContent));
                $this->assertSame('true', $link->firstChild->getAttribute('aria-hidden'));
            }
        }
    }
}

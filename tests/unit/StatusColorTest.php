<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class StatusColorTest extends CIUnitTestCase
{
    public function testUserPropertyLinkKeepsItsDestinationAndAccessibleName(): void
    {
        $xpath = $this->xpath(view('usuarios/management_content', [
            'allUsers' => [[
                'id' => 42,
                'nombre' => 'Ana',
                'apellido' => 'Prueba',
                'email' => 'ana@example.test',
                'telefono' => '',
                'role' => 'Propietario',
                'activo' => true,
                'estado_solicitud' => 'Aprobada',
            ]],
            'pendingUsers' => [],
            'currentAdminId' => 0,
        ]));

        $links = $xpath->query('//a[@href="' . site_url('usuarios/42/inmuebles') . '"]');
        $this->assertSame(1, $links->length);
        $link = $links->item(0);
        $this->assertSame('Ver inmuebles de Ana Prueba', $link->getAttribute('aria-label'));
        $this->assertSame('Ver inmuebles', $link->getAttribute('title'));
        $this->assertStringContainsString('crud-button--icon', $link->getAttribute('class'));
        $this->assertSame('apartment', trim($link->textContent));
        $this->assertSame('true', $xpath->query('.//span', $link)->item(0)->getAttribute('aria-hidden'));
    }

    public function testUserBadgesKeepTheirTextAndDistinguishInactiveFromRejected(): void
    {
        $users = [];
        foreach (['Aprobada', 'Pendiente', 'Rechazada', 'Aprobada'] as $index => $state) {
            $users[] = [
                'id' => $index + 1,
                'nombre' => 'Usuario',
                'apellido' => (string) $index,
                'email' => "user{$index}@example.test",
                'telefono' => '',
                'role' => 'Propietario',
                'activo' => $index !== 3,
                'estado_solicitud' => $state,
            ];
        }

        $xpath = $this->xpath(view('usuarios/management_content', [
            'allUsers' => $users,
            'pendingUsers' => [],
            'currentAdminId' => 0,
        ]));

        foreach (['aprobada' => 'Aprobada', 'pendiente' => 'Pendiente', 'rechazada' => 'Rechazada', 'baja' => 'Baja'] as $state => $text) {
            $badges = $xpath->query('//span[@data-state-domain="usuario" and @data-state="' . $state . '"]');
            $this->assertSame(1, $badges->length);
            $this->assertSame($text, trim($badges->item(0)->textContent));
        }
    }

    public function testAssociationBadgesUseTheProvidedEffectiveState(): void
    {
        $properties = [];
        foreach (['Activo', 'Pausado', 'En Revisión', 'Inactivo'] as $index => $state) {
            $properties[] = [
                'id' => $index + 1,
                'asociacion_id' => $index + 1,
                'alias_nombre' => 'Casa',
                'tipo' => 'Casa',
                'calle' => 'Calle',
                'numero' => '1',
                'piso_dpto' => null,
                'ciudad' => 'Ciudad',
                'estado_servicio' => $state,
                'estado_inmueble' => 'Ocupado',
                'estado_actividad' => $state === 'Inactivo' ? 'Inactivo' : 'Activo',
                'titular_inmueble' => 'Titular',
                'nro_medidor' => null,
            ];
        }
        $service = [
            'id' => 1,
            'nombre' => 'Agua',
            'categoria' => 'Agua',
            'proveedor' => 'Proveedor',
            'unidad_medida_std' => 'm3',
            'inmuebles' => $properties,
            'inmueble_ids' => [1, 2, 3, 4],
        ];
        $xpath = $this->xpath(view('servicios/index', [
            'servicios' => [$service],
            'catalogoServicios' => [],
            'inmuebles' => [],
            'formData' => [],
            'errors' => [],
            'success' => null,
            'error' => null,
            'resumen' => ['total' => 1, 'asociaciones' => 4, 'inmuebles' => 4],
            'serviceLocked' => false,
        ]));

        foreach (['activo' => 'Activo', 'pausado' => 'Pausado', 'en revisión' => 'En Revisión', 'inactivo' => 'Inactivo'] as $state => $text) {
            $badges = $xpath->query('//span[@data-state-domain="vinculo" and @data-state="' . $state . '"]');
            $this->assertSame(1, $badges->length);
            $this->assertSame('Vínculo: ' . $text, trim($badges->item(0)->textContent));
        }
        $this->assertSame(3, $xpath->query('//span[@data-state-domain="inmueble" and @data-state="ocupado"]')->length);
        $this->assertSame(1, $xpath->query('//span[@data-state-domain="inmueble" and @data-state="dado de baja"]')->length);
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }
}

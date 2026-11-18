<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class LandingPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $namespace = 'App';

    public function testLandingIsThePublicEntryPointAndExplainsTheProduct(): void
    {
        $response = $this->get('/');

        $response->assertOK();
        $response->assertSee('Tus inmuebles y servicios');
        $response->assertSee('De la propiedad al informe');
        $response->assertSee('Marcelo Baldome');
        $response->assertSee('Rodrigo Quichan');
        $response->assertSee('Jairo Crespo');
        $response->assertSee('href="' . esc(base_url('login'), 'attr') . '"', false);
        $response->assertSee('href="' . esc(base_url('sing_up'), 'attr') . '"', false);
    }

    public function testLoginRemainsAvailableOnItsOwnPublicRoute(): void
    {
        $response = $this->get('login');

        $response->assertOK();
        $response->assertSee('Iniciar Sesión');
        $response->assertSee('action="' . esc(base_url('login'), 'attr') . '"', false);
    }
}

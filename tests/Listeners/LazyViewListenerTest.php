<?php

namespace Bdf\Templating\Listeners;

use Bdf\Templating\_files\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class LazyViewListenerTest extends TestCase
{
    private Kernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = new TestKernel([
            function (ContainerConfigurator $configurator) {
                $configurator->services()
                    ->set(ViewListener::class)
                    ->autowire()
                    ->public()
                ;

                $configurator->services()
                    ->set(LazyViewListener::class)
                    ->args([service('service_container'), ViewListener::class])
                    ->autowire()
                    ->tag('kernel.event_subscriber')
                ;
            }
        ]);
        $this->kernel->boot();
    }

    public function test_home()
    {
        $response = $this->kernel->handle(Request::create('/'));

        $this->assertSame('hello World', $response->getContent());
        $this->assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
    }

    public function test_home_json()
    {
        $request = Request::create('/');
        $request->headers->set('Accept', 'application/json');
        $response = $this->kernel->handle($request);

        $this->assertSame('{"name": "World"}', $response->getContent());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
    }

    public function test_controller_return_string()
    {
        $response = $this->kernel->handle(Request::create('/string'));

        $this->assertSame('hello World', $response->getContent());
        $this->assertSame('text/html; charset=UTF-8', $response->headers->get('Content-Type'));
    }
}

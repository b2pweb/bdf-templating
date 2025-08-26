<?php

namespace Bdf\Templating\Bundle;

use Bdf\Templating\_files\TestKernel;
use Bdf\Templating\Bundle\_files\MyExtension;
use Bdf\Templating\ConfigurableViewInterface;
use Bdf\Templating\EngineInterface;
use Bdf\Templating\PhpEngine;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;

class BdfTemplatingBundleTest extends TestCase
{
    private TestKernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = new TestKernel([
            function (ContainerConfigurator $configurator) {
                $configurator->extension('bdf_templating', [
                    'template_directory' => __DIR__.'/_files/templates',
                    'default_layout' => 'layout',
                    'helper_namespaces' => [
                        'Bdf\\Templating\\Bundle\\_files\\helpers'
                    ]
                ]);

                $configurator->services()->set(MyExtension::class)->tag('bdf.templating.extension');
            }
        ]);
        $this->kernel->boot();
    }

    public function test_rendering()
    {
        $response = $this->kernel->handle(Request::create('http://example.com/'));

        $this->assertSame(<<<'HTML'
<!DOCTYPE html>
<html>
    <head>
        <title>Test</title>
    </head>
    <body>
        <header>
            <a href="/">Home</a>
        </header>
        <h1>Home page</h1>
<p>Hello World!</p>
<p>test</p>
<p>42</p>
    </body>
</html>

HTML
, $response->getContent());
    }

    public function test_services()
    {
        $this->assertInstanceOf(PhpEngine::class, $this->kernel->getContainer()->get(EngineInterface::class));
        $this->assertInstanceOf(PhpEngine::class, $this->kernel->getContainer()->get(ConfigurableViewInterface::class));
    }
}

<?php

namespace Bdf\Templating\Bundle;

use Bdf\Templating\_files\TestKernel;
use Bdf\Templating\Bundle\_files\BrokenNameHelper;
use Bdf\Templating\Bundle\_files\CustomIdHelper;
use Bdf\Templating\Bundle\_files\GreetingHelper;
use Bdf\Templating\Bundle\_files\MissingParentService;
use Bdf\Templating\Bundle\_files\MyExtension;
use Bdf\Templating\Helpers\Css;
use Bdf\Templating\ConfigurableViewInterface;
use Bdf\Templating\EngineInterface;
use Bdf\Templating\PhpEngine;
use Bdf\Templating\Test\TestEngine;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;

class BdfTemplatingBundleTest extends TestCase
{
    private const PAGE_ASSETS_HTML = <<<'HTML'
<html>
<head><link href="/page.css" type="text/css" rel="stylesheet" /></head>
<body>
<main><p>fragment</p>
</main>
<script src="/page.js" ></script><script src="/fragment.js" ></script></body>
</html>

HTML;

    private TestKernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = $this->createKernel();
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

    public function test_assets_with_default_package()
    {
        $kernel = $this->createKernel([
            'assets' => [
                'base_path' => '/assets',
                'version' => 'v1',
            ],
        ]);

        $this->assertSame(<<<'HTML'
<head><link href="/assets/style.css?v1" type="text/css" rel="stylesheet" /></head>
<body><script src="/assets/app.js?v1" ></script></body>
<img src="/assets/logo.png?v1" />
<script src="/assets/other.js?v1"></script>
<link href="/assets/other.css?v1" />

HTML
, $kernel->getContainer()->get(EngineInterface::class)->render('assets'));
    }

    public function test_assets_with_js_and_css_packages()
    {
        $kernel = $this->createKernel([
            'assets' => [
                'base_path' => '/assets',
                'packages' => [
                    'js' => ['base_path' => '/js'],
                    'css' => ['base_urls' => ['https://cdn.example.com/css']],
                ],
            ],
        ]);

        $this->assertSame(<<<'HTML'
<head><link href="https://cdn.example.com/css/style.css" type="text/css" rel="stylesheet" /></head>
<body><script src="/js/app.js" ></script></body>
<img src="/assets/logo.png" />
<script src="/js/other.js"></script>
<link href="https://cdn.example.com/css/other.css" />

HTML
, $kernel->getContainer()->get(EngineInterface::class)->render('assets'));
    }

    public function test_helper_declared_as_service_with_dependencies()
    {
        $response = $this->kernel->handle(Request::create('http://example.com/render/route_helper'));

        $this->assertSame("<p>render</p>\n", $response->getContent());
    }

    public function test_fragment()
    {
        $response = $this->kernel->handle(Request::create('http://example.com/render/fragment'));

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
        <p>Hello Bob</p>
<p>Hello Alice</p>
    </body>
</html>

HTML
, $response->getContent());
    }

    public function test_session_and_flash()
    {
        $kernel = $this->createKernel([
            'session' => [
                'storage_factory_id' => 'session.storage.factory.mock_file',
            ],
        ]);

        $response = $kernel->handle(Request::create('http://example.com/session', 'POST', ['user' => 'Bob', 'flash' => 'Saved!']));
        $this->assertTrue($response->isRedirect('/render/session'));

        $cookies = [];
        foreach ($response->headers->getCookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }
        $this->assertNotEmpty($cookies);

        $response = $kernel->handle(Request::create('http://example.com/render/session', 'GET', [], $cookies));
        $this->assertSame(<<<'HTML'
<p>User: Bob</p>
<p class="success">Saved!</p>

HTML
, $response->getContent());

        // The flash message is consumed, but the session attribute is kept
        $response = $kernel->handle(Request::create('http://example.com/render/session', 'GET', [], $cookies));
        $this->assertSame("<p>User: Bob</p>\n", $response->getContent());
    }

    public function test_helpers_are_reset_between_requests()
    {
        // Handle multiple requests with the same kernel, like in worker mode (FrankenPHP, RoadRunner...)
        // The js added by the fragment must be rendered by the page layout, and not be duplicated on the next request
        $this->assertSame(self::PAGE_ASSETS_HTML, $this->kernel->handle(Request::create('http://example.com/render/page_assets'))->getContent());
        $this->assertSame(self::PAGE_ASSETS_HTML, $this->kernel->handle(Request::create('http://example.com/render/page_assets'))->getContent());
    }

    public function test_helpers_are_reset_after_an_error()
    {
        $response = $this->kernel->handle(Request::create('http://example.com/render/error_assets'));
        $this->assertSame(500, $response->getStatusCode());

        $this->assertSame(self::PAGE_ASSETS_HTML, $this->kernel->handle(Request::create('http://example.com/render/page_assets'))->getContent());
    }

    public function test_helper_service_outside_helper_namespaces_is_declared_by_name()
    {
        $response = $this->kernel->handle(Request::create('http://example.com/render/greet_helper'));

        $this->assertSame("<p>Hello Bob (42)</p>\n", $response->getContent());
    }

    public function test_helper_service_with_custom_id_is_declared_by_name()
    {
        $response = $this->kernel->handle(Request::create('http://example.com/render/custom_id_helper'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("<p>42</p>\n", $response->getContent());
    }

    public function test_helper_with_unresolvable_name_is_ignored()
    {
        // The kernel is booted, and other helpers still works
        $this->assertSame('<p>Hello Bob (42)</p>'."\n", $this->kernel->getContainer()->get(EngineInterface::class)->render('greet_helper'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Helper "Broken" not found.');
        $this->kernel->getContainer()->get(EngineInterface::class)->getHelper('broken');
    }

    public function test_helpers_are_not_shared()
    {
        $container = $this->kernel->getContainer();

        $this->assertNotSame($container->get(Css::class), $container->get(Css::class));
        $this->assertNotSame($container->get(GreetingHelper::class), $container->get(GreetingHelper::class));
    }

    public function test_test_engine_is_disabled_by_default()
    {
        $this->assertInstanceOf(PhpEngine::class, $this->kernel->getContainer()->get(EngineInterface::class));
    }

    public function test_test_engine_enabled_by_configuration()
    {
        $kernel = $this->createKernel(bdfConfig: ['test' => true]);
        $container = $kernel->getContainer();

        $engine = $container->get(EngineInterface::class);
        $this->assertInstanceOf(TestEngine::class, $engine);
        $this->assertSame($container->get(TestEngine::class), $engine);
        $this->assertInstanceOf(PhpEngine::class, $engine->getEngine());
    }

    public function test_test_engine_records_rendered_views()
    {
        $kernel = $this->createKernel(bdfConfig: ['test' => true]);

        $response = $kernel->handle(Request::create('http://example.com/render/greet_helper?foo=bar'));
        $this->assertSame("<p>Hello Bob (42)</p>\n", $response->getContent());

        /** @var TestEngine $engine */
        $engine = $kernel->getContainer()->get(TestEngine::class);
        $this->assertSame('greet_helper', $engine->getView());
        $this->assertSame(['foo' => 'bar'], $engine->getParameters());

        $kernel->handle(Request::create('http://example.com/render/route_helper'));
        $this->assertSame('route_helper', $engine->getView());
        $this->assertSame([
            'greet_helper' => ['foo' => 'bar'],
            'route_helper' => [],
        ], $engine->getViews());
    }

    public function test_test_engine_with_render_disabled()
    {
        $kernel = $this->createKernel(bdfConfig: ['test' => true]);

        /** @var TestEngine $engine */
        $engine = $kernel->getContainer()->get(TestEngine::class);
        $engine->setEnabledRender(false);
        $engine->setDefaultRender('stub');

        $response = $kernel->handle(Request::create('http://example.com/render/not_found?foo=bar'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('stub', $response->getContent());
        $this->assertSame('not_found', $engine->getView());
        $this->assertSame(['foo' => 'bar'], $engine->getParameters());
    }

    public function test_test_engine_keeps_helpers_reset()
    {
        $kernel = $this->createKernel(bdfConfig: ['test' => true]);

        $this->assertSame(self::PAGE_ASSETS_HTML, $kernel->handle(Request::create('http://example.com/render/page_assets'))->getContent());
        $this->assertSame(self::PAGE_ASSETS_HTML, $kernel->handle(Request::create('http://example.com/render/page_assets'))->getContent());
    }

    public function test_services_with_missing_parent_class_should_be_ignored()
    {
        $kernel = new TestKernel([
            function (ContainerConfigurator $configurator) {
                $configurator->extension('bdf_templating', [
                    'template_directory' => __DIR__.'/_files/templates',
                ]);

                $services = $configurator->services();
                $services->set('missing_parent', MissingParentService::class);
                $services->set(MyExtension::class)->autowire()->autoconfigure();
                $services->set(GreetingHelper::class)->autowire()->autoconfigure();
                $configurator->parameters()->set('missing_parent.class', MissingParentService::class);
            }
        ]);
        $kernel->boot();

        $this->assertInstanceOf(GreetingHelper::class, $kernel->getContainer()->get(GreetingHelper::class));
    }

    private function createKernel(array $frameworkConfig = [], array $bdfConfig = []): TestKernel
    {
        $kernel = new TestKernel([
            function (ContainerConfigurator $configurator) use ($frameworkConfig, $bdfConfig) {
                $configurator->extension('bdf_templating', [
                    'template_directory' => __DIR__.'/_files/templates',
                    'default_layout' => 'layout',
                    'helper_namespaces' => [
                        'Bdf\\Templating\\Bundle\\_files\\helpers'
                    ]
                ] + $bdfConfig);

                if ($frameworkConfig) {
                    $configurator->extension('framework', $frameworkConfig);
                }

                // Load helpers like a real project would do, i.e. with autowire and autoconfigure, but not explicitly public
                $configurator->services()
                    ->defaults()->autowire()->autoconfigure()
                    ->load('Bdf\\Templating\\Bundle\\_files\\helpers\\', __DIR__.'/_files/helpers')
                ;

                $configurator->services()->set(MyExtension::class)->tag('bdf.templating.extension');

                // Helpers outside the helper namespaces : must be declared on the engine by the bundle
                $services = $configurator->services()->defaults()->autowire()->autoconfigure();
                $services->set(GreetingHelper::class);
                $services->set('app.custom_id_helper', CustomIdHelper::class);
                $services->set(BrokenNameHelper::class);
            }
        ]);
        $kernel->boot();

        return $kernel;
    }
}

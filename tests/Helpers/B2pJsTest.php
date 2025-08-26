<?php

namespace Bdf\Templating\Helpers;

use Bdf\Templating\PhpEngine;
use Bdf\Templating\TemplateResolver\TemplateResolverInterface;
use DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_Helpers
 */
class B2pJsTest extends TestCase
{
    /**
     * @var B2pJs
     */
    private $helper;

    /**
     *
     */
    public function setUp(): void
    {
        $view = new PhpEngine(new Container(), $this->createMock(TemplateResolverInterface::class));

        $this->helper = new B2pJs();
        $this->helper->setView($view);
    }

    /**
     *
     */
    public function test_name()
    {
        $this->assertEquals("b2pJs", $this->helper->getName());
    }

    /**
     *
     */
    public function test_empty()
    {
        $this->helper->importJs();
        $this->assertEquals('', $this->helper->helper("js")->render());
    }

    /**
     *
     */
    public function test_module()
    {
        $this->helper->useModule("JsModule", ["foo" => "bar"]);
        $this->helper->importJs();

        $expected = '<script>B2p.Config.merge({"JsModule":{"foo":"bar"}});B2p.Modules.use(["JsModule"]);</script>';

        $this->assertEquals($expected, $this->helper->helper("js")->render());
    }

    /**
     *
     */
    public function test_module_with_closure_config()
    {
        $this->helper->useModule("JsModule", ["foo" => "function() {return 'bar';}"]);
        $this->helper->importJs();

        $expected = '<script>B2p.Config.merge({"JsModule":{"foo":function() {return \'bar\';}}});'
                    .'B2p.Modules.use(["JsModule"]);</script>';

        $this->assertEquals($expected, $this->helper->helper("js")->render());
    }

    /**
     *
     */
    public function test_empty_config()
    {
        $this->helper->config("JsModule");

        $this->assertEquals('', $this->callMethod($this->helper, "renderConfigs"));
    }

    /**
     *
     */
    public function test_module_as_config()
    {
        $this->helper->config(["JsModule" => ["foo" => "bar"]]);

        $expected = 'B2p.Config.merge({"JsModule":{"foo":"bar"}});';

        $this->assertEquals($expected, $this->callMethod($this->helper, "renderConfigs"));
    }

    /**
     *
     */
    public function test_config_with_value()
    {
        $this->helper->config("JsModule", "bar");

        $expected = 'B2p.Config.merge({"JsModule":"bar"});';

        $this->assertEquals($expected, $this->callMethod($this->helper, "renderConfigs"));
    }

    /**
     *
     */
    public function test_import_file()
    {
        $this->helper->registerPath(__DIR__."/_files/%s.js");
        $this->helper->importModule("JsModule");
        $this->helper->importJs();

        $expected = '<script>var tested=true;</script>';

        $this->assertEquals($expected, $this->helper->helper("js")->render());
    }

    private function callMethod(object $object, string $method, array $parameters = [])
    {
        $closure = function (...$args) use ($object, $method) {
            return $object->$method(...$args);
        };
        $closure = $closure->bindTo(null, get_class($object));

        return $closure(...$parameters);
    }
}

<?php

namespace Bdf\Templating;

use Bdf\Templating\Asset\PathAsset;
use Bdf\Templating\Filters\FilterInterface;
use Bdf\Templating\Helpers\B2pJs;
use Bdf\Templating\Helpers\HelperInterface;
use Bdf\Templating\Helpers\ResettableHelperInterface;
use Bdf\Templating\Helpers\TestHelper;
use Bdf\Templating\TemplateResolver\FilesystemResolver;
use DI\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_PhpEngine
 */
class PhpEngineTest extends TestCase
{
    protected $di;
    /** @var FilesystemResolver */
    protected $resolver;
    /** @var PhpEngine */
    protected $engine;
    
    /**
     * 
     */
    protected function setUp(): void
    {
        $this->di = new Container();
        $this->resolver = new FilesystemResolver(__DIR__.'/_files/templates');
        $this->engine = new PhpEngine($this->di, $this->resolver);
        
    }
    
    /**
     * 
     */
    public function test_default_values()
    {
        $this->assertEquals('base', $this->engine->getDefaultLayout());
        $this->assertEquals('.html.php', $this->engine->getViewSuffix());
        $this->assertEquals([], $this->engine->getHelperNamespaces());
    }
    
    /**
     * 
     */
    public function test_set_get_default_layout()
    {
        $this->engine->setDefaultLayout('test');
        $this->assertEquals('test', $this->engine->getDefaultLayout());
    }
    
    /**
     * 
     */
    public function test_set_get_view_suffix()
    {
        $this->engine->setViewSuffix('.xml.php');
        $this->assertEquals('.xml.php', $this->engine->getViewSuffix());
    }
    
    /**
     * 
     */
    public function test_add_get_helper()
    {
        $helper = $this->createMock(HelperInterface::class);
        $helper->expects($this->once())->method('getName')->will($this->returnValue('test'));
        
        $this->engine->addHelper($helper);
        $this->assertSame($helper, $this->engine->getHelper('test'));
    }

    public function test_getHelper_by_class_name()
    {
        $helper = $this->engine->getHelper(B2pJs::class);
        $this->assertInstanceOf(B2pJs::class, $helper);

        $this->assertSame($helper, $this->engine->getHelper(B2pJs::class));
        $this->assertSame($helper, $this->engine->getHelper('b2pJs'));
    }

    public function test_getHelper_from_container()
    {
        $this->di->set(B2pJs::class, $helper = new B2pJs());

        $this->assertSame($helper, $this->engine->getHelper(B2pJs::class));
        $this->assertSame($helper, $this->engine->getHelper('b2pJs'));
    }

    /**
     * 
     */
    public function test_call_helper()
    {
        $helper = $this->createMock(HelperInterface::class);
        $helper->expects($this->once())->method('getName')->will($this->returnValue('test'));
        
        $this->engine->addHelper($helper);
        $this->assertSame($helper, $this->engine->test());
    }

    /**
     * 
     */
    public function test_add_get_namespace()
    {
        $this->engine->setHelperNamespaces([
            '',
            'namespace',
        ]);
        $this->assertSame(['', 'namespace\\'], $this->engine->getHelperNamespaces());
    }
    
    /**
     * 
     */
    public function test_prepend_namespace()
    {
        $this->engine->addHelperNamespace('namespace');
        $this->engine->prependHelperNamespace('test\\');
        $this->assertSame(['test\\', 'namespace\\'], $this->engine->getHelperNamespaces());
    }
    
    /**
     * 
     */
    public function test_prepend_empty_namespace()
    {
        $this->engine->addHelperNamespace('namespace');
        $this->engine->prependHelperNamespace('');
        $this->assertSame(['', 'namespace\\'], $this->engine->getHelperNamespaces());
    }

    /**
     *
     */
    public function test_helper_not_found()
    {
        $this->expectException(\RuntimeException::class);

        $this->engine->testHelper();
    }

    /**
     *
     */
    public function test_load_helper()
    {
        include_once __DIR__."/_files/helpers/TestHelper.php";

        $this->engine->addHelperNamespace(__NAMESPACE__."\\Helpers");
        $helper = $this->engine->testHelper();

        $this->assertInstanceOf(TestHelper::class, $helper);
        $this->assertSame($this->engine, $helper->view());

        $this->assertSame("bar", $helper->foo(), "Testing initialize method call");
        $this->assertTrue($helper->isPrepared(), "Testing prepare method call");
    }

    /**
     *
     */
    public function test_declare_helper()
    {
        include_once __DIR__."/_files/helpers/TestHelper.php";

        $this->engine->declareHelper("testHelper", TestHelper::class);
        $helper = $this->engine->testHelper();

        $this->assertInstanceOf(TestHelper::class, $helper);
    }

    /**
     *
     */
    public function test_basic_render()
    {
        $content = $this->engine->render('basic', ['name' => 'John']);

        $this->assertEquals('hello John', $content);
    }

    /**
     *
     */
    public function test_render_remove_vars()
    {
        $this->engine->render('basic', ['name' => 'John']);

        $this->assertSame([], $this->engine->vars());
    }

    /**
     *
     */
    public function test_layout_render()
    {
        $content = $this->engine->render('with-layout', ['name' => 'John']);

        $this->assertEquals('<html>hello John</html>', $content);
    }

    /**
     *
     */
    public function test_multiple_extends_render()
    {
        $content = $this->engine->render('child', ['name' => 'John']);

        $this->assertEquals('<html>hello John</html>', $content);
    }

    /**
     *
     */
    public function test_multiple_render()
    {
        $content = $this->engine->render('multiple-render', ['name' => 'Doe']);

        $this->assertEquals('<html><html>hello John</html> Doe</html>', $content);
    }

    /**
     *
     */
    public function test_partial_render()
    {
        $content = $this->engine->render('partial', ['name' => 'John']);

        $this->assertEquals('hello John', $content);
    }

    /**
     *
     */
    public function test_parts_helper()
    {
        $content = $this->engine->render('sidemenu', ['name' => 'John']);

        $this->assertEquals('item1 - item2 hello John', $content);
    }

    /**
     *
     */
    public function test_current_suffix()
    {
        $this->engine->setViewSuffix('.json.php');
        $content = $this->engine->render('basic.json.php', ['name' => 'John']);

        $this->assertEquals('{"name": "John"}', $content);
    }

    /**
     *
     */
    public function test_magic_method()
    {
        $this->assertFalse(isset($this->engine->value));
    }

    /**
     *
     */
    public function test_filters()
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->expects($this->once())->method("filter")->with("body")->willReturn("filtered");

        $this->engine->addFilter($filter);

        $this->assertEquals("filtered", $this->engine->filter("body"));
    }

    /**
     *
     */
    public function test_set_filters()
    {
        $filter1 = $this->createMock(FilterInterface::class);
        $filter1->expects($this->once())->method("filter")->with("body")->willReturn("filtered");
        $filter2 = $this->createMock(FilterInterface::class);
        $filter2->expects($this->once())->method("filter")->with("filtered")->willReturn("filtered2");

        $this->engine->setFilters([$filter1, $filter2]);

        $this->assertEquals("filtered2", $this->engine->filter("body"));
    }

    public function test_helper_methods()
    {
        $this->assertSame('&lt;', $this->engine->escape('<'));
        $this->assertSame('&lt;', $this->engine->e('<'));
        $this->assertSame('{"key":"value"}', $this->engine->json(['key' => 'value']));

        $this->di->set('asset', new PathAsset());
        $this->assertSame('/path/to/asset', $this->engine->asset()->getUrl('path/to/asset'));
    }

    public function test_extension_methods()
    {
        $this->engine->addExtension($ext = new class {
            public function shout($string): string
            {
                return strtoupper($string) . '!';
            }
        });

        $this->assertSame('HELLO!', $this->engine->shout('hello'));
        $this->assertSame(['shout' => $ext], (fn () => $this->__extensionMethods)->bindTo($this->engine, PhpEngine::class)());
        $this->assertSame('WORLD!', $this->engine->shout('world'));
    }

    public function test_fragment()
    {
        $fragmentRenderer = $this->createMock(FragmentRendererInterface::class);
        $fragmentRenderer->expects($this->once())->method('render')->with('/path', $this->isInstanceOf(Request::class), ['ignore_errors' => true])->willReturn(new Response('fragment content'));
        $fragmentRenderer->expects($this->once())->method('getName')->willReturn('inline');
        $fragmentHandler = new FragmentHandler(
            $rs = new RequestStack(),
            [$fragmentRenderer],
        );
        $rs->push(Request::createFromGlobals());
        $this->engine = new PhpEngine(
            $this->di,
            $this->resolver,
            fn () => $fragmentHandler,
        );

        $this->assertSame('fragment content', $this->engine->fragment('/path'));
    }

    public function test_helpers_are_reset_after_render()
    {
        $expected = '<style type="text/css">.page {}</style><script>page();</script>';

        $this->assertSame($expected, $this->engine->render('reset-assets'));
        $this->assertTrue($this->engine->css()->isEmpty());
        $this->assertTrue($this->engine->js()->isEmpty());

        // The state of the previous render is not kept
        $this->assertSame($expected, $this->engine->render('reset-assets'));
    }

    public function test_helpers_are_not_reset_by_nested_renders()
    {
        // The layout and the sub-template are rendered while the root template is still rendering,
        // so the scripts added by the content and the sub-template must be available in the layout
        $this->assertSame('sub|<script>content();</script><script>sub();</script>', $this->engine->render('reset-content'));
        $this->assertTrue($this->engine->js()->isEmpty());
    }

    public function test_reset_is_called_once_per_root_render()
    {
        $this->engine->addHelper($helper = $this->resetCounterHelper());

        // Contains a nested render and two layouts
        $this->engine->render('multiple-render', ['name' => 'Doe']);
        $this->assertSame(1, $helper->resets);

        $this->engine->render('basic', ['name' => 'John']);
        $this->assertSame(2, $helper->resets);
    }

    public function test_helper_state_defined_before_render_is_used_then_reset()
    {
        $this->engine->js()->appendTag('before();');

        $this->assertSame('<script>before();</script>', $this->engine->render('reset-js'));
        $this->assertTrue($this->engine->js()->isEmpty());
    }

    public function test_parts_are_reset_after_render()
    {
        $this->assertSame('item1 - item2 hello John', $this->engine->render('sidemenu', ['name' => 'John']));

        $this->assertFalse($this->engine->parts()->get('sidemenu'));
        $this->assertFalse($this->engine->parts()->get('_content'));
    }

    public function test_b2pjs_state_is_reset_after_render()
    {
        $this->engine->render('reset-use-module');

        $this->assertSame('', $this->engine->render('reset-import-js'));
    }

    public function test_b2pjs_module_paths_are_kept_after_render()
    {
        // Module paths are a configuration of the helper, done once when the engine is created
        $helper = new B2pJs();
        $helper->registerPath(__DIR__.'/Helpers/_files/%s.js');
        $this->engine->addHelper($helper);

        $this->assertSame('<script>var tested=true;</script>', $this->engine->render('reset-module'));
        $this->assertSame('<script>var tested=true;</script>', $this->engine->render('reset-module'));
    }

    public function test_state_is_reset_after_exception()
    {
        $this->engine->addHelper($helper = $this->resetCounterHelper());
        $level = ob_get_level();

        try {
            $this->engine->render('reset-error', ['foo' => 'bar']);
            $this->fail('An exception should be thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame('Error in template', $e->getMessage());
        }

        $this->assertSame($level, ob_get_level());
        $this->assertSame([], $this->engine->vars());
        $this->assertSame(1, $helper->resets);
        $this->assertTrue($this->engine->js()->isEmpty());

        // The engine is still usable
        $this->assertSame('<style type="text/css">.page {}</style><script>page();</script>', $this->engine->render('reset-assets'));
        $this->assertSame(2, $helper->resets);
    }

    public function test_state_is_reset_after_exception_in_layout()
    {
        $this->engine->addHelper($helper = $this->resetCounterHelper());

        try {
            $this->engine->render('reset-error-in-layout');
            $this->fail('An exception should be thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame('Error in layout', $e->getMessage());
        }

        $this->assertSame(1, $helper->resets);
        $this->assertTrue($this->engine->js()->isEmpty());

        // Following renders must still reset the helpers
        $this->engine->render('reset-assets');
        $this->assertSame(2, $helper->resets);
        $this->assertTrue($this->engine->js()->isEmpty());
    }

    public function test_state_is_reset_after_exception_in_part()
    {
        $level = ob_get_level();

        try {
            $this->engine->render('reset-error-in-part');
            $this->fail('An exception should be thrown');
        } catch (\RuntimeException $e) {
            $this->assertSame('Error in part', $e->getMessage());
        }

        // Close the leaked buffers before asserting, to keep other tests clean
        $leakedBuffers = ob_get_level() - $level;
        while (ob_get_level() > $level) {
            ob_end_clean();
        }

        $this->assertSame(0, $leakedBuffers, 'The output buffer opened by the part must be closed');
        $this->assertFalse($this->engine->parts()->get('part'));
    }

    private function resetCounterHelper(): ResettableHelperInterface
    {
        return new class implements ResettableHelperInterface {
            public int $resets = 0;

            public function getName(): string
            {
                return 'resetCounter';
            }

            public function reset(): void
            {
                ++$this->resets;
            }
        };
    }
}

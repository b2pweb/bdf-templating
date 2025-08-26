<?php

namespace Bdf\Templating;

use Bdf\Templating\Asset\PathAsset;
use Bdf\Templating\Filters\FilterInterface;
use Bdf\Templating\Helpers\B2pJs;
use Bdf\Templating\Helpers\HelperInterface;
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
}

<?php

namespace Bdf\Templating\Test;

use Bdf\Templating\ConfigurableViewInterface;
use Bdf\Templating\EngineInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_Test
 */
class TestEngineTest extends TestCase
{
    /**
     * @var TestEngine
     */
    protected $engine;
    /**
     * @var EngineInterface
     */
    protected $wrappedEngine;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->engine = new TestEngine(
            $this->wrappedEngine = $this->createMock(CustomTestEngine::class)
        );
    }
    
    /**
     * 
     */
    public function test_get_engine()
    {
        $this->assertSame($this->wrappedEngine, $this->engine->getEngine());
    }

    /**
     *
     */
    public function test_set_view_suffix()
    {
        $this->wrappedEngine->expects($this->once())->method("setViewSuffix")->with(".html");
        $this->engine->setViewSuffix(".html");
    }

    /**
     *
     */
    public function test_set_default_layout()
    {
        $this->wrappedEngine->expects($this->once())->method("setDefaultLayout")->with("layout");
        $this->engine->setDefaultLayout("layout");
    }

    /**
     *
     */
    public function test_set_get_render_enabled()
    {
        $this->assertFalse($this->engine->isRenderDisabled());
        $this->engine->setEnabledRender(false);
        $this->assertTrue($this->engine->isRenderDisabled());
    }

    /**
     *
     */
    public function test_basic_render()
    {
        $this->wrappedEngine->expects($this->once())
            ->method('render')
            ->with('view-name', ['view->parameter' => 'test'])
            ->willReturn('rendered content')
        ;

        $this->engine->render('view-name', ['view->parameter' => 'test']);
    }

    /**
     * 
     */
    public function test_setter_getter_default_render()
    {
        $render = 'default render';
        
        $this->engine->setDefaultRender($render);
        
        $this->assertEquals($render, $this->engine->getDefaultRender());
    }
    
    /**
     * 
     */
    public function test_render_returns_engine_render()
    {
        $render = 'engine rendered';
        
        $this->wrappedEngine->expects($this->once())
            ->method('render')
            ->will($this->returnValue($render));
        
        $this->assertEquals($render, $this->engine->render(''));
    }
    
    /**
     * 
     */
    public function test_default_render()
    {
        $render = 'tessssssssssssst';
        
        $this->engine->setDefaultRender($render);
        $this->engine->setEnabledRender(false);
        
        $this->assertEquals($render, $this->engine->render(''));
    }
    
    /**
     * 
     */
    public function test_render_set_view_name_and_parameters()
    {
        $this->wrappedEngine->expects($this->once())->method('render')->willReturn('rendered content');
        $this->engine->render('view-name', ['view->parameter' => 'test']);

        $this->assertEquals('view-name', $this->engine->getView());
        $this->assertEquals(['view->parameter' => 'test'], $this->engine->getParameters());
        $this->assertEquals(['view->parameter' => 'test'], $this->engine->getViewParameters('view-name'));
        $this->assertEquals(['view-name' => ['view->parameter' => 'test']], $this->engine->getViews());
    }
}


class CustomTestEngine implements EngineInterface, ConfigurableViewInterface
{
    /**
     * Set the view suffix
     *
     * @param string $viewSuffix
     */
    public function setViewSuffix($viewSuffix)
    {
        // TODO: Implement setViewSuffix() method.
    }

    /**
     * Set the default layout name
     *
     * @param string $defaultLayout
     */
    public function setDefaultLayout($defaultLayout)
    {
        // TODO: Implement setDefaultLayout() method.
    }

    /**
     * Render a template
     *
     * @param string $name
     * @param array $parameters
     *
     * @return string
     */
    public function render($name, array $parameters = array())
    {
        // TODO: Implement render() method.
    }
}

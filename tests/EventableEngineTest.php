<?php

namespace Bdf\Templating;

use Bdf\Templating\Events\Events;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_EventableEngineTest
 */
class EventableEngineTest extends TestCase
{
    private $engine;
    private $wrappedEngine;
    private $dispatcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->dispatcher = new EventDispatcher();
        
        $this->engine = new EventableEngine(
            $this->wrappedEngine = $this->createMock(CustomTestEngine::class),
            $this->dispatcher
        );
    }

    /**
     *
     */
    public function test_set_get_dispatcher()
    {
        $this->assertSame($this->dispatcher, $this->engine->getEventDispatcher());

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->engine->setEventDispatcher($dispatcher);

        $this->assertSame($dispatcher, $this->engine->getEventDispatcher());
    }

    /**
     *
     */
    public function test_set_get_engine()
    {
        $this->assertSame($this->wrappedEngine, $this->engine->getEngine());

        $engine = $this->createMock(EngineInterface::class);
        $this->engine->setEngine($engine);

        $this->assertSame($engine, $this->engine->getEngine());
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
    public function test_basic_render()
    {
        $this->wrappedEngine->expects($this->once())
            ->method('render')
            ->with('view-name', ['view->parameter' => 'test'])
            ->willReturn('content')
        ;
        
        $this->engine->render('view-name', ['view->parameter' => 'test']);
    }
    
    /**
     * 
     */
    public function test_pre_render_is_called()
    {
        $eventCalled = false;

        $this->wrappedEngine->expects($this->once())->method('render')->willReturn('content');
        $this->dispatcher->addListener(Events::PRE_RENDER, function($event) use(&$eventCalled) {
            $eventCalled = true;
            
            $this->assertEquals('view-name', $event->getView());
            $this->assertEquals(['view->parameter' => 'test'], $event->getParameters());
        });
        
        $this->engine->render('view-name', ['view->parameter' => 'test']);
        
        $this->assertTrue($eventCalled, 'pre render is not called');
    }
    
    /**
     * 
     */
    public function test_pre_render_can_change_view()
    {
        $this->dispatcher->addListener(Events::PRE_RENDER, function($event) {
            $event->setView('view-changed');
        });
        
        $this->wrappedEngine->expects($this->once())
            ->method('render')
            ->with('view-changed')
            ->willReturn('content')
        ;
        
        $this->engine->render('view-name');
    }
    
    /**
     * 
     */
    public function test_pre_render_can_change_parameters()
    {
        $this->dispatcher->addListener(Events::PRE_RENDER, function($event) {
            $event->setParameters(['test' => 'value-changed']);
        });
        
        $this->wrappedEngine->expects($this->once())
            ->method('render')
            ->with('', ['test' => 'value-changed'])
            ->willReturn('content')
        ;
        
        $this->engine->render('', ['test' => 'value']);
    }
    
    /**
     * 
     */
    public function test_pre_render_can_change_engine()
    {
        $engine = $this->createMock('Bdf\Templating\EngineInterface');
        $engine->expects($this->once())->method('render')->willReturn('content');
        
        $this->dispatcher->addListener(Events::PRE_RENDER, function($event) use($engine) {
            $event->setEngine($engine);
        });
        
        $this->wrappedEngine->expects($this->never())->method('render');
        
        $this->engine->render('');
    }
    
    /**
     * 
     */
    public function test_post_render_is_called()
    {
        $eventCalled = false;

        $this->wrappedEngine->expects($this->once())->method('render')->willReturn('content');
        $this->dispatcher->addListener(Events::POST_RENDER, function($event) use(&$eventCalled) {
            $eventCalled = true;
        });
        
        $this->engine->render('');
        
        $this->assertTrue($eventCalled, 'post render is not called');
    }
    
    /**
     * 
     */
    public function test_post_render_received_engine_render()
    {
        $this->dispatcher->addListener(Events::POST_RENDER, function($event) {
            $this->assertEquals('content', $event->getContent());
        });
        
        $this->wrappedEngine->expects($this->once())
            ->method('render')
            ->will($this->returnValue('content'));
        
        $this->engine->render('');
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

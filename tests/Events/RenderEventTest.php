<?php

namespace Bdf\Templating\Events;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_Events
 */
class RenderEventTest extends TestCase
{
    /**
     * 
     */
    public function test_default_values()
    {
        $event = new RenderEvent('template');
        
        $this->assertEquals('template', $event->getView());
        $this->assertEquals([], $event->getParameters());
        $this->assertEquals(null, $event->getEngine());
    }
    
    /**
     * 
     */
    public function test_constructor_values()
    {
        $engine = $this->createMock('Bdf\Templating\EngineInterface');
        $event = new RenderEvent('template', ['key' => 'value'], $engine);
        
        $this->assertEquals('template', $event->getView());
        $this->assertEquals(['key' => 'value'], $event->getParameters());
        $this->assertSame($engine, $event->getEngine());
    }
    
    /**
     * 
     */
    public function test_set_get_engine()
    {
        $engine = $this->createMock('Bdf\Templating\EngineInterface');
        
        $event = new RenderEvent('');
        
        $event->setEngine($engine);
        $this->assertSame($engine, $event->getEngine());
    }
    
    /**
     * 
     */
    public function test_set_get_parameters()
    {
        $event = new RenderEvent('');
        
        $event->setParameters(['key' => 'value']);
        $this->assertSame(['key' => 'value'], $event->getParameters());
    }
    
    /**
     * 
     */
    public function test_set_get_view()
    {
        $event = new RenderEvent('');
        
        $event->setView('template');
        $this->assertSame('template', $event->getView());
    }
}

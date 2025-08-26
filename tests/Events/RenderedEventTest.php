<?php

namespace Bdf\Templating\Events;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_Events
 */
class RenderedEventTest extends TestCase
{
    /**
     * 
     */
    public function test_default_values()
    {
        $event = new RenderedEvent('content');
        
        $this->assertEquals('content', $event->getContent());
        $this->assertEquals(null, $event->getEngine());
    }
    
    /**
     * 
     */
    public function test_set_get_content()
    {
        $event = new RenderedEvent('');
        
        $event->setContent('content');
        $this->assertEquals('content', $event->getContent());
    }
    
    /**
     * 
     */
    public function test_get_engine()
    {
        $engine = $this->createMock('Bdf\Templating\EngineInterface');
        
        $event = new RenderedEvent('', $engine);
        
        $this->assertSame($engine, $event->getEngine());
    }
}

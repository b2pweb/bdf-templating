<?php

namespace Bdf\Templating;

use Bdf\Templating\Events\Events;
use Bdf\Templating\Events\RenderEvent;
use Bdf\Templating\Events\RenderedEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * EventableEngine
 */
class EventableEngine implements EngineInterface, ConfigurableViewInterface
{
    public function __construct(
        protected EngineInterface $engine,
        protected EventDispatcherInterface $eventDispatcher
    ) {}

    /**
     * Get the engine
     *
     * @return EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Set the engine
     *
     * @param EngineInterface $engine
     * @deprecated The class should be immutable. Use constructor injection instead.
     */
    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Get the event dispatcher
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Set the event dispatcher
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @deprecated The class should be immutable. Use constructor injection instead.
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultLayout($defaultLayout): void
    {
        if ($this->engine instanceof ConfigurableViewInterface) {
            $this->engine->setDefaultLayout($defaultLayout);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setViewSuffix($viewSuffix): void
    {
        if ($this->engine instanceof ConfigurableViewInterface) {
            $this->engine->setViewSuffix($viewSuffix);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function render($name, array $parameters = []): string
    {
        // Add event as 2nd argument for compatibility with symfony/event-dispatcher and legacy code
        $this->eventDispatcher->dispatch(
            $event = new RenderEvent($name, $parameters, $this->engine),
            Events::PRE_RENDER
        );

        $content = $event->engine->render($event->view, $event->parameters);

        $this->eventDispatcher->dispatch(
            $event = new RenderedEvent($content, $this->engine),
            Events::POST_RENDER
        );

        return $event->content;
    }
}

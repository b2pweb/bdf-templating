<?php

namespace Bdf\Templating\Events;

use Bdf\Templating\EngineInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Rendering has not yet been performed
 * The view can be configured using this event
 */
final class RenderEvent implements StoppableEventInterface
{
    private bool $propagationStopped = false;

    public function __construct(
        /**
         * The view template name
         */
        public string $view,
        public array $parameters = [],
        public ?EngineInterface $engine = null
    ) {}

    /**
     * {@inheritdoc}
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * @param string $view
     * @deprecated Use property directly instead
     */
    public function setView($view)
    {
        $this->view = $view;
    }
    
    /**
     * @return string
     * @deprecated Use property directly instead
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param array $parameters
     * @deprecated Use property directly instead
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     * @deprecated Use property directly instead
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param EngineInterface $engine
     * @deprecated Use property directly instead
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * @return EngineInterface
     * @deprecated Use property directly instead
     */
    public function getEngine()
    {
        return $this->engine;
    }
}

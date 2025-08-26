<?php

namespace Bdf\Templating\Events;

use Bdf\Templating\EngineInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * The template has been fully rendered
 */
final class RenderedEvent implements StoppableEventInterface
{
    private bool $propagationStopped = false;

    public function __construct(
        /**
         * The rendered content.
         * It can be changed by event listeners.
         */
        public string $content,

        /**
         * The engine used for rendering (if any)
         *
         * @var EngineInterface|null
         */
        public readonly ?EngineInterface $engine = null
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
     * @param string $content
     * @deprecated Use property directly instead
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    /**
     * @return string
     * @deprecated Use property directly instead
     */
    public function getContent()
    {
        return $this->content;
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

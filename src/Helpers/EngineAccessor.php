<?php

namespace Bdf\Templating\Helpers;

use Bdf\Templating\EngineInterface;

/**
 * Implements accessor to view engine inside helper
 */
trait EngineAccessor
{
    protected ?EngineInterface $view = null;

    /**
     * Get helper
     *
     * @param string|class-string<H> $name
     *
     * @return HelperInterface
     * @psalm-return HelperInterface
     * @template H as HelperInterface
     */
    public function helper(string $name): HelperInterface
    {
        return $this->view->getHelper($name);
    }

    /**
     * Set view engine
     */
    public function setView(EngineInterface $view): void
    {
        $this->view = $view;
    }

    /**
     * Get view engine
     */
    public function view(): ?EngineInterface
    {
        return $this->view;
    }
}

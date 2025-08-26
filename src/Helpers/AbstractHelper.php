<?php

namespace Bdf\Templating\Helpers;

use Bdf\Templating\EngineInterface;
use Psr\Container\ContainerInterface;

/**
 * @deprecated Do not use
 */
abstract class AbstractHelper implements HelperInterface
{
    use EngineAccessor;

    protected ?ContainerInterface $di = null;

    public function setDI(?ContainerInterface $di): void
    {
        $this->di = $di;
    }

    /**
     * Get a service from the container
     *
     * @param string $service
     * @return mixed
     *
     * @deprecated Use dependency injection instead
     */
    public function get(string $service): mixed
    {
        assert($this->di !== null);

        return $this->di->get($service);
    }

    /**
     * Create an instance of the given item using DI instantiator
     *
     * @param mixed $item
     * @param array $parameters
     * @return mixed
     *
     * @deprecated Use dependency injection instead
     */
    public function make(mixed $item, array $parameters = []): mixed
    {
        assert($this->di !== null);

        return $this->di->get('di.instantiator')->make($item, $parameters);
    }

    /**
     * Get helper
     *
     * @param string $name
     *
     * @return HelperInterface
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

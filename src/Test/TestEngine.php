<?php

namespace Bdf\Templating\Test;

use Bdf\Templating\EngineInterface;
use Bdf\Templating\ConfigurableViewInterface;

/**
 * TestEngine
 */
class TestEngine implements EngineInterface, ConfigurableViewInterface
{
    /**
     * Current parameters
     */
    protected ?array $parameters = [];
    
    /**
     * Current view
     * 
     * @var string
     */
    protected ?string $view = null;
    
    /**
     * All parameters by views
     * 
     * @var array
     */
    protected array $views = [];
    
    /**
     * @var bool
     */
    private bool $enabledRender = true;
    
    /**
     * @var string
     */
    private string $defaultRender = '';

    public function __construct(
        /**
         * The inner engine
         */
        protected readonly EngineInterface $engine,
    ) {}
    
    /**
     * @return array
     */
    public function getViews()
    {
        return $this->views;
    }
    
    /**
     * @return array
     */
    public function getViewParameters($view)
    {
        return $this->views[$view] ?? [];
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @return EngineInterface
     */
    public function getEngine(): EngineInterface
    {
        return $this->engine;
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
     * @param bool $flag
     */
    public function setEnabledRender($flag): void
    {
        $this->enabledRender = (bool) $flag;
    }
    
    /**
     * @return bool
     */
    public function isRenderDisabled(): bool
    {
        return $this->enabledRender === false;
    }
    
    /**
     * @param string $defaultRender
     */
    public function setDefaultRender($defaultRender): void
    {
        $this->defaultRender = $defaultRender;
    }
    
    /**
     * @return string
     */
    public function getDefaultRender(): string
    {
        return $this->defaultRender;
    }
    
    /**
     * {@inheritdoc}
     */
    public function render($name, array $parameters = []): string
    {
        $this->parameters = $parameters;
        $this->view = $name;
        $this->views[$name] = $parameters;
        
        if ($this->enabledRender) {
            return $this->engine->render($name, $parameters);
        }
        
        return $this->defaultRender;
    }
}

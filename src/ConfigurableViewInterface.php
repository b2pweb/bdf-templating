<?php

namespace Bdf\Templating;

/**
 * @package Bdf\Templating
 */
interface ConfigurableViewInterface
{
    /**
     * Set the view suffix
     * 
     * @param string $viewSuffix
     */
    public function setViewSuffix($viewSuffix);
    
    /**
     * Set the default layout name
     * 
     * @param string $defaultLayout
     */
    public function setDefaultLayout($defaultLayout);
}

<?php

namespace Bdf\Templating;

/**
 * @package Bdf\Templating
 */
interface EngineInterface
{
    /**
     * Render a template
     * 
     * @param string $name
     * @param array $parameters
     *
     * @return string
     */
    public function render($name, array $parameters = []);
}

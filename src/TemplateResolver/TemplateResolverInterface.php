<?php

namespace Bdf\Templating\TemplateResolver;

/**
 * @package Bdf\Templating\TemplateResolver
 */
interface TemplateResolverInterface
{
    /**
     * @param string $template
     *
     * @return string
     */
    public function resolve($template);
}

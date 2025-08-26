<?php

namespace Bdf\Templating\Filters;

/**
 * @since %VERSION%
 * @deprecated filter system is deprecated, do not use it anymore
 */
interface FilterInterface
{
    /**
     * Filter string
     * 
     * @param string $buffer
     * @return string
     */
    public function filter($buffer);
}

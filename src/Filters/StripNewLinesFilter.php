<?php

namespace Bdf\Templating\Filters;

/**
 * @since %VERSION%
 * @deprecated filter system is deprecated, do not use it anymore
 */
class StripNewLinesFilter implements FilterInterface
{
    /**
     * @see FilterInterface::filter
     */
    public function filter($buffer)
    {
        return str_replace(array("\n", "\r"), '', $buffer);
    }
}

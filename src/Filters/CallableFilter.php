<?php

namespace Bdf\Templating\Filters;

/**
 * @since %VERSION%
 * @deprecated filter system is deprecated, do not use it anymore
 */
class CallableFilter implements FilterInterface
{
    /**
     * @var callable
     */
    protected $callback;
    
    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }
    
    /**
     * @see FilterInterface::filter
     */
    public function filter($value)
    {
        $callback = $this->callback;
        return $callback($value);
    }
}

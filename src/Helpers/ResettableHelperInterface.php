<?php

namespace Bdf\Templating\Helpers;

/**
 * Base type for helper with an internal state that should be cleared after the rendering.
 * The reset method will be called after the render call of the root template.
 */
interface ResettableHelperInterface extends HelperInterface
{
    /**
     * Reset the helper state.
     */
    public function reset(): void;
}

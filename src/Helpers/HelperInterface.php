<?php

namespace Bdf\Templating\Helpers;

/**
 * 
 */
interface HelperInterface
{
    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName();
}

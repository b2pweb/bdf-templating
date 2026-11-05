<?php

namespace Bdf\Templating\Bundle\_files;

use Bdf\Templating\Helpers\HelperInterface;

/**
 * Helper whose name cannot be resolved without calling the constructor
 */
class BrokenNameHelper implements HelperInterface
{
    private string $name;

    public function __construct()
    {
        $this->name = 'broken';
    }

    public function getName(): string
    {
        return $this->name;
    }
}

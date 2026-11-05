<?php

namespace Bdf\Templating\Bundle\_files;

use Bdf\Templating\Helpers\HelperInterface;

/**
 * Helper outside the configured helper namespaces, with a name different from its class name
 */
class GreetingHelper implements HelperInterface
{
    public function __construct(
        private readonly MyExtension $extension,
    ) {}

    public function getName(): string
    {
        return 'greet';
    }

    public function hello(string $name): string
    {
        return 'Hello '.$name.' ('.$this->extension->foo().')';
    }
}

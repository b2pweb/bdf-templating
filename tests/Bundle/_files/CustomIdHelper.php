<?php

namespace Bdf\Templating\Bundle\_files;

use Bdf\Templating\Helpers\HelperInterface;

/**
 * Helper registered with a service id which is not its class name
 */
class CustomIdHelper implements HelperInterface
{
    public function __construct(
        private readonly MyExtension $extension,
    ) {}

    public function getName(): string
    {
        return 'customId';
    }

    public function value(): int
    {
        return $this->extension->foo();
    }
}

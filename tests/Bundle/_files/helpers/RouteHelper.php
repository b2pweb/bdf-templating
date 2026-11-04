<?php

namespace Bdf\Templating\Bundle\_files\helpers;

use Bdf\Templating\Helpers\HelperInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Helper with a constructor dependency : can only work if it is resolved from the container
 */
class RouteHelper implements HelperInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {}

    public function getName(): string
    {
        return 'routeHelper';
    }

    public function current(): ?string
    {
        return $this->requestStack->getCurrentRequest()?->attributes->get('_route');
    }
}

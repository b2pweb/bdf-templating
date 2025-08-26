<?php

namespace Bdf\Templating\Extensions;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Extension get session values on the template
 */
final class Session
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {}

    public function flash(): ?FlashBagInterface
    {
        $session = $this->requestStack->getSession();

        if ($session instanceof FlashBagAwareSessionInterface) {
            return $session->getFlashBag();
        }

        return null;
    }

    public function session(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}

<?php

namespace Bdf\Templating\Extensions;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Extension for URL generation and request information
 */
final class Url
{
    public function __construct(
        private readonly UrlGeneratorInterface $generator,
        private readonly RequestStack $requestStack,
    ) {}

    public function url(string $route, array $parameters = []): string
    {
        return $this->generator->generate($route, $parameters);
    }

    public function absoluteUrl(string $route, array $parameters = []): string
    {
        return $this->generator->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function getCurrentRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    public function basePath(): string
    {
        return $this->getCurrentRequest()->getBasePath();
    }

    public function absoluteBasePath(): string
    {
        $request = $this->getCurrentRequest();

        return $request->getSchemeAndHttpHost() . $request->getBasePath();
    }

    public function baseUrl(): string
    {
        return $this->getCurrentRequest()->getBaseUrl();
    }

    public function absoluteBaseUrl(): string
    {
        $request = $this->getCurrentRequest();

        return $request->getSchemeAndHttpHost() . $request->getBaseUrl();
    }
}

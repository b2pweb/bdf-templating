<?php

namespace Bdf\Templating\Extensions;

use Bdf\Templating\PhpEngine;
use Bdf\Templating\TemplateResolver\FilesystemResolver;
use DI\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class UrlTest extends TestCase
{
    private PhpEngine $engine;
    private Request $request;

    protected function setUp(): void
    {
        $this->engine = new PhpEngine(
            new Container(),
            new FilesystemResolver(__DIR__ . '/../_files/templates'),
            null,
            [
                new Url(
                    self::createUrlGenerator(),
                    $rs = new RequestStack()
                )
            ]
        );

        $rs->push($this->request = Request::create('http://example.com/base/path?foo=bar'));
    }

    public function test_url()
    {
        $this->assertSame('/base/user/42', $this->engine->url('user_profile', ['id' => 42]));
        $this->assertSame('/base/', $this->engine->url('home', []));
        $this->assertSame('/base/article', $this->engine->url('article_show', []));
    }

    public function test_absoluteUrl()
    {
        $this->assertSame('http://example.com/base/user/42', $this->engine->absoluteUrl('user_profile', ['id' => 42]));
    }

    public function test_getCurrentRequest()
    {
        $request = $this->engine->getCurrentRequest();
        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('http://example.com/base/path?foo=bar', $request->getUri());
    }

    public function test_basePath()
    {
        $this->assertSame('', $this->engine->basePath());
    }

    public function test_absoluteBasePath()
    {
        $this->assertSame('http://example.com', $this->engine->absoluteBasePath());
    }

    public function test_baseUrl()
    {
        $this->assertSame('', $this->engine->baseUrl());
    }

    public function test_absoluteBaseUrl()
    {
        $this->assertSame('http://example.com', $this->engine->absoluteBaseUrl());
    }

    private static function createUrlGenerator(): UrlGeneratorInterface
    {
        $routes = new RouteCollection();
        $routes->add('home', new Route('/'));
        $routes->add('user_profile', new Route('/user/{id}'));
        $routes->add('article_show', new Route('/article/{slug}', ['slug' => null]));

        return new UrlGenerator(
            $routes,
            new RequestContext(
                '/base',
                'GET',
                'example.com',
            )
        );
    }
}

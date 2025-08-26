<?php

namespace Bdf\Templating\Extensions;

use Bdf\Templating\PhpEngine;
use Bdf\Templating\TemplateResolver\FilesystemResolver;
use DI\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionTest extends TestCase
{
    private Request $request;

    protected function setUp(): void
    {
        $this->engine = new PhpEngine(
            new Container(),
            new FilesystemResolver(__DIR__ . '/../_files/templates'),
            null,
            [
                new Session(
                    new RequestStack([$this->request = Request::createFromGlobals()])
                ),
            ]
        );
    }

    public function test_session()
    {
        $this->request->setSession($session = new \Symfony\Component\HttpFoundation\Session\Session());
        $this->assertSame($session, $this->engine->session());
    }

    public function test_flash()
    {
        $this->request->setSession($session = new \Symfony\Component\HttpFoundation\Session\Session());
        $this->assertSame($session->getFlashBag(), $this->engine->flash());
    }
}

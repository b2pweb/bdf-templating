<?php

namespace Bdf\Templating\Extensions;

use Bdf\Templating\PhpEngine;
use Bdf\Templating\TemplateResolver\FilesystemResolver;
use DI\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class SessionTest extends TestCase
{
    private Request $request;
    private PhpEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new PhpEngine(
            new Container(),
            new FilesystemResolver(__DIR__ . '/../_files/templates'),
            null,
            [
                new Session(
                    $rs = new RequestStack()
                ),
            ]
        );

        $rs->push($this->request = Request::createFromGlobals());
    }

    public function test_session()
    {
        $this->request->setSession($session = new \Symfony\Component\HttpFoundation\Session\Session(new MockArraySessionStorage()));
        $this->assertSame($session, $this->engine->session());
    }

    public function test_flash()
    {
        $this->request->setSession($session = new \Symfony\Component\HttpFoundation\Session\Session(new MockArraySessionStorage()));
        $this->assertSame($session->getFlashBag(), $this->engine->flash());
    }
}

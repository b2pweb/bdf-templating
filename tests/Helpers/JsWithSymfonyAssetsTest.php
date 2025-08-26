<?php

namespace Bdf\Templating\Helpers;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_Helpers
 */
class JsWithSymfonyAssetsTest extends TestCase
{
    /**
     * @var Js
     */
    private $helper;

    /**
     *
     */
    public function setUp(): void
    {
        $this->helper = new Js(new Packages(new PathPackage('/', new EmptyVersionStrategy())));
    }

    /**
     *
     */
    public function test_default()
    {
        $this->assertTrue($this->helper->isEmpty());
        $this->assertEquals('', $this->helper->render());
    }

    /**
     *
     */
    public function test_name()
    {
        $this->assertEquals("js", $this->helper->getName());
    }

    /**
     *
     */
    public function test_append_file()
    {
        $this->helper->append("file.js");

        $this->assertEquals('<script src="/file.js" ></script>', $this->helper->render());
    }

    /**
     *
     */
    public function test_prepend_file()
    {
        $this->helper->append("file1.js");
        $this->helper->prepend("file2.js");

        $expected = '<script src="/file2.js" ></script>'
            .'<script src="/file1.js" ></script>';

        $this->assertEquals($expected, $this->helper->render());
    }

    /**
     *
     */
    public function test_append_js()
    {
        $this->helper->appendTag("var test;");

        $this->assertEquals('<script>var test;</script>', $this->helper->render());
    }

    /**
     *
     */
    public function test_link_conditionnal()
    {
        $result = $this->helper->link("file.js", [], "IE9");

        $this->assertEquals('<!--[if IE9]><script src="/file.js" ></script><![endif]-->', $result);
    }

    /**
     *
     */
    public function test_tag_conditionnal()
    {
        $result = $this->helper->tag("var test;", "IE9");

        $this->assertEquals('<!--[if IE9]><script>var test;</script><![endif]-->', $result);
    }
}

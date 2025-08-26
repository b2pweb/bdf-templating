<?php

namespace Bdf\Templating\Helpers;

use Bdf\Templating\Asset\PathAsset;
use DI\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_Helpers
 */
class CssWithSymfonyAssetsTest extends TestCase
{
    /**
     * @var Css
     */
    private $helper;

    /**
     *
     */
    public function setUp(): void
    {
        $this->helper = new Css(new Packages(new PathPackage('/', new EmptyVersionStrategy())));
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
        $this->assertEquals("css", $this->helper->getName());
    }

    /**
     *
     */
    public function test_add_file()
    {
        $this->helper->add("file.css");

        $this->assertEquals('<link href="/file.css" type="text/css" rel="stylesheet" />', $this->helper->render());
    }

    /**
     *
     */
    public function test_prepend_file()
    {
        $this->helper->add("file1.css");
        $this->helper->prepend("file2.css");

        $expected = '<link href="/file2.css" type="text/css" rel="stylesheet" />'
                    .'<link href="/file1.css" type="text/css" rel="stylesheet" />';

        $this->assertEquals($expected, $this->helper->render());
    }

    /**
     *
     */
    public function test_add_css()
    {
        $this->helper->addTag(".class {}");

        $this->assertEquals('<style type="text/css">.class {}</style>', $this->helper->render());
    }

    /**
     *
     */
    public function test_link_conditionnal()
    {
        $result = $this->helper->link("file.css", [], "IE9");

        $this->assertEquals('<!--[if IE9]><link href="/file.css" type="text/css" rel="stylesheet" /><![endif]-->', $result);
    }

    /**
     *
     */
    public function test_tag_conditionnal()
    {
        $result = $this->helper->tag(".class {}", "IE9");

        $this->assertEquals('<!--[if IE9]><style type="text/css">.class {}</style><![endif]-->', $result);
    }
}

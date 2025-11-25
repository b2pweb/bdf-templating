<?php

namespace Bdf\Templating\Extensions;

use DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_Extensions
 */
class EscaperTest extends TestCase
{
    /**
     * @var Engine
     */
    private $engine;

    /**
     *
     */
    public function setUp(): void
    {
        $container = new Container(['charset' => 'UTF-8']);
        $this->engine = new Engine($container);
    }

    /**
     *
     */
    public function test_dont_escape_non_string()
    {
        $this->assertSame('1', $this->engine->escape(1));
    }

    /**
     *
     */
    public function test_convert()
    {
        $value = 'testé';
        $expected = 'testÃ©';

        $this->assertSame($expected, $this->engine->convertEncoding($value, 'UTF-8', 'ISO-8859-1'));
    }

    /**
     * @dataProvider htmlProvider
     */
    public function test_html_escaping($key, $value)
    {
        $this->assertSame($value, $this->engine->e($key), 'Failed to escape: '.$key);
    }
    public static function htmlProvider()
    {
        return [
            /* Encode chars as named entities where possible */
            ['\'', '&#039;'],
            ['"', '&quot;'],
            ['<', '&lt;'],
            ['>', '&gt;'],
            ['&', '&amp;'],
            /* Characters beyond ASCII value 255 to unicode escape */
            ['Ā', 'Ā'],
            /* Characters beyond Unicode BMP to unicode escape */
            ["\xF0\x90\x80\x80", "\xF0\x90\x80\x80"],
            /* Immune chars excluded */
            [',', ','],
            ['.', '.'],
            ['-', '-'],
            ['_', '_'],
            /* Basic alnums exluded */
            ['a', 'a'],
            ['A', 'A'],
            ['z', 'z'],
            ['Z', 'Z'],
            ['0', '0'],
            ['9', '9'],
            /* Basic control characters and null */
            ["\r", "\r"],
            ["\n", "\n"],
            ["\t", "\t"],
            ["\0", "\0"],
            [' ', ' '],
        ];
    }

    /**
     *
     * @dataProvider htmlAttrProvider
     */
    public function test_html_attr_escaping($key, $value)
    {
        $this->assertSame($value, $this->engine->eAttr($key), 'Failed to escape: '.$key);
        $this->assertSame($value, $this->engine->escapeAttr($key));
    }
    public static function htmlAttrProvider()
    {
        return [
            /* Encode chars as named entities where possible */
            ['\'', '&#x27;'],
            ['"', '&quot;'],
            ['<', '&lt;'],
            ['>', '&gt;'],
            ['&', '&amp;'],
            /* Characters beyond ASCII value 255 to unicode escape */
            ['Ā', '&#x0100;'],
            /* Characters beyond Unicode BMP to unicode escape */
            ["\xF0\x90\x80\x80", '&#x10000;'],
            /* Immune chars excluded */
            [',', ','],
            ['.', '.'],
            ['-', '-'],
            ['_', '_'],
            /* Basic alnums exluded */
            ['a', 'a'],
            ['A', 'A'],
            ['z', 'z'],
            ['Z', 'Z'],
            ['0', '0'],
            ['9', '9'],
            /* Basic control characters and null */
            ["\r", '&#x0D;'],
            ["\n", '&#x0A;'],
            ["\t", '&#x09;'],
            ["\0", '&#xFFFD;'], // should use Unicode replacement char
            /* Encode spaces for quoteless attribute protection */
            [' ', '&#x20;'],
        ];
    }

    /**
     * @dataProvider jsProvider
     */
    public function test_js_escaping($key, $value)
    {
        $this->assertSame($value, $this->engine->eJs($key), 'Failed to escape: '.$key);
        $this->assertSame($value, $this->engine->escapeJs($key));
    }

    public static function jsProvider()
    {
        return [
            /* HTML special chars - escape without exception to hex */
            ['\'', '\\x27'],
            ['"', '\\x22'],
            ['<', '\\x3C'],
            ['>', '\\x3E'],
            ['&', '\\x26'],
            /* Characters beyond ASCII value 255 to unicode escape */
            ['Ā', '\\u0100'],
            /* Characters beyond Unicode BMP to unicode escape */
            ["\xF0\x90\x80\x80", '\\uD800\\uDC00'],
            /* Immune chars excluded */
            [',', ','],
            ['.', '.'],
            ['_', '_'],
            /* Basic alnums exluded */
            ['a', 'a'],
            ['A', 'A'],
            ['z', 'z'],
            ['Z', 'Z'],
            ['0', '0'],
            ['9', '9'],
            /* Basic control characters and null */
            ["\r", '\\x0D'],
            ["\n", '\\x0A'],
            ["\t", '\\x09'],
            ["\0", '\\x00'],
            /* Encode spaces for quoteless attribute protection */
            [' ', '\\x20'],
        ];
    }

    /**
     * @dataProvider cssProvider
     */
    public function test_css_escaping($key, $value)
    {
        $this->assertSame($value, $this->engine->eCss($key), 'Failed to escape: '.$key);
        $this->assertSame($value, $this->engine->escapeCss($key));
    }
    public static function cssProvider()
    {
        return [
            /* HTML special chars - escape without exception to hex */
            ['\'', '\\27 '],
            ['"', '\\22 '],
            ['<', '\\3C '],
            ['>', '\\3E '],
            ['&', '\\26 '],
            /* Characters beyond ASCII value 255 to unicode escape */
            ['Ā', '\\100 '],
            /* Characters beyond Unicode BMP to unicode escape */
            ["\xF0\x90\x80\x80", '\\10000 '],
            /* Immune chars excluded */
            [',', '\\2C '],
            ['.', '\\2E '],
            ['_', '\\5F '],
            /* Basic alnums exluded */
            ['a', 'a'],
            ['A', 'A'],
            ['z', 'z'],
            ['Z', 'Z'],
            ['0', '0'],
            ['9', '9'],
            /* Basic control characters and null */
            ["\r", '\\D '],
            ["\n", '\\A '],
            ["\t", '\\9 '],
            ["\0", '\\0 '],
            /* Encode spaces for quoteless attribute protection */
            [' ', '\\20 '],
        ];
    }

    /**
     * @dataProvider urlProvider
     */
    public function test_url_escaping($key, $value)
    {
        $this->assertSame($value, $this->engine->eUrl($key), 'Failed to escape: '.$key);
        $this->assertSame($value, $this->engine->escapeUrl($key));
    }
    public static function urlProvider()
    {
        return [
            /* HTML special chars - escape without exception to percent encoding */
            ['\'', '%27'],
            ['"', '%22'],
            ['<', '%3C'],
            ['>', '%3E'],
            ['&', '%26'],
            /* Characters beyond ASCII value 255 to hex sequence */
            ['Ā', '%C4%80'],
            /* Punctuation and unreserved check */
            [',', '%2C'],
            ['.', '.'],
            ['_', '_'],
            ['-', '-'],
            [':', '%3A'],
            [';', '%3B'],
            ['!', '%21'],
            /* Basic alnums excluded */
            ['a', 'a'],
            ['A', 'A'],
            ['z', 'z'],
            ['Z', 'Z'],
            ['0', '0'],
            ['9', '9'],
            /* Basic control characters and null */
            ["\r", '%0D'],
            ["\n", '%0A'],
            ["\t", '%09'],
            ["\0", '%00'],
            /* PHP quirks from the past */
            [' ', '%20'],
            ['~', '~'],
            ['+', '%2B'],
        ];
    }
}


//------------------

class Engine
{
    use Escaper;

    private $di;

    public function __construct($di)
    {
        $this->di = $di;
    }
}

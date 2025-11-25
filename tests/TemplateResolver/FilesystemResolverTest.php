<?php

namespace Bdf\Templating\TemplateResolver;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_TemplateResolver
 * @group Bdf_Templating_TemplateResolver_FilesystemResolver
 */
class FilesystemResolverTest extends TestCase
{
    /**
     * @dataProvider resolveProvider
     */
    public function test_resolve($directory, $template, $expected)
    {
        $this->assertEquals(
            $expected,
            (new FilesystemResolver($directory))->resolve($template)
        );
    }

    /**
     * @dataProvider resolveExceptionsProvider
     */
    public function test_resolve_exceptions_when_not_found($directory, $template, $exceptionType)
    {
        $this->expectException($exceptionType);

        (new FilesystemResolver($directory))->resolve($template);
    }

    /**
     * @return array
     */
    public static function resolveProvider()
    {
        $directory     = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
        $subdirectory1 = $directory . 'folder1' . DIRECTORY_SEPARATOR;
        $subdirectory2 = $directory . 'folder2' . DIRECTORY_SEPARATOR;

        return array(
            array(
                $directory,
                'test.html.php',
                $directory . 'test.html.php'
            ),

            array(
                array($subdirectory1, $subdirectory2),
                'test1.html.php',
                $subdirectory1 . 'test1.html.php'
            ),

            array(
                array($subdirectory1, $subdirectory2),
                'test2.html.php',
                $subdirectory2 . 'test2.html.php'
            )
        );
    }

    /**
     * @return array
     */
    public static function resolveExceptionsProvider()
    {
        $directory     = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR;
        $subdirectory1 = $directory . 'folder1' . DIRECTORY_SEPARATOR;
        $subdirectory2 = $directory . 'folder2' . DIRECTORY_SEPARATOR;

        return array(
            array(
                $directory,
                'not-found.html.php',
                'Bdf\\Templating\\Exception\\InvalidArgumentException'
            ),

            array(
                array($subdirectory1, $subdirectory2),
                'not-found.html.php',
                'Bdf\\Templating\\Exception\\InvalidArgumentException'
            )
        );
    }
}

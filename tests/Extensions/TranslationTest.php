<?php

namespace Bdf\Templating\Extensions;

use Bdf\Templating\PhpEngine;
use Bdf\Templating\TemplateResolver\FilesystemResolver;
use DI\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Stringable;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationTest extends TestCase
{
    private PhpEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new PhpEngine(
            new Container(),
            new FilesystemResolver(__DIR__ . '/../_files/templates'),
            null,
            [
                new Translation(self::createTranslator(...))
            ]
        );
    }

    public function test_underscore_method()
    {
        $this->assertSame('Hello', $this->engine->_('Hello'));
        $this->assertSame('Bonjour', $this->engine->_('Hello', [], 'messages', 'fr'));
        $this->assertSame('Hello John', $this->engine->_('Hello %name%', ['%name%' => 'John']));
        $this->assertSame('Hello John', $this->engine->translate('Hello %name%', ['%name%' => 'John']));
    }

    public function test_underscore_method_with_list()
    {
        $this->assertSame('Value: foo', $this->engine->_('Value: %s', ['foo']));
        $this->assertSame('Valeur : bar', $this->engine->_('Value: %s', ['bar'], 'messages', 'fr'));
        $this->assertSame('Valeur : bar', $this->engine->_('Value: %s', 'bar', 'messages', 'fr'));
    }

    public function test_n_method()
    {
        $this->assertSame('No items', $this->engine->_n('{0}No items|{1}One item|]1,Inf]Many items', 0));
        $this->assertSame('One item', $this->engine->_n('{0}No items|{1}One item|]1,Inf]Many items', 1));
        $this->assertSame('Many items', $this->engine->_n('{0}No items|{1}One item|]1,Inf]Many items', 5));
        $this->assertSame('Aucun élément', $this->engine->_n('{0}No items|{1}One item|]1,Inf]Many items', 0, [], 'messages', 'fr'));
        $this->assertSame('Un élément', $this->engine->_n('{0}No items|{1}One item|]1,Inf]Many items', 1, [], 'messages', 'fr'));
        $this->assertSame('Plusieurs éléments', $this->engine->_n('{0}No items|{1}One item|]1,Inf]Many items', 3, [], 'messages', 'fr'));
        $this->assertSame('No items', $this->engine->translatePlural('{0}No items|{1}One item|]1,Inf]Many items', 0));
    }

    public function test_translatable_interface()
    {
        $translatable = new class implements TranslatableInterface {
            public function trans(TranslatorInterface $translator, string $locale = null): string
            {
                return 'custom';
            }
        };
        $this->assertSame('custom', $this->engine->_($translatable));
        $this->assertSame('custom', $this->engine->_n($translatable, 1));
    }

    public function test_stringable_id()
    {
        $id = new class implements Stringable {
            public function __toString(): string { return 'Hello'; }
        };
        $this->assertSame('Hello', $this->engine->_($id));
    }

    private static function createTranslator(): Translator
    {
        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', [
            'Hello' => 'Hello',
            'Hello %name%' => 'Hello %name%',
            '{0}No items|{1}One item|]1,Inf]Many items' => '{0}No items|{1}One item|]1,Inf]Many items',
            'Value: %s' => 'Value: %s',
        ], 'en');
        $translator->addResource('array', [
            'Hello' => 'Bonjour',
            'Hello %name%' => 'Bonjour %name%',
            '{0}No items|{1}One item|]1,Inf]Many items' => '{0}Aucun élément|{1}Un élément|]1,Inf]Plusieurs éléments',
            'Value: %s' => 'Valeur : %s',
        ], 'fr');
        return $translator;
    }
}

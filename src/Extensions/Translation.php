<?php

namespace Bdf\Templating\Extensions;

use Closure;
use Stringable;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_is_list;
use function is_array;
use function vsprintf;

/**
 * Extension which provides translation capabilities to the templating engine.
 */
final class Translation
{
    public function __construct(
        /**
         * The translator service or a factory to get it lazily.
         *
         * @var TranslatorInterface|Closure():TranslatorInterface
         */
        private Closure|TranslatorInterface $translator,
    ) {}

    /**
     * @param string|Stringable|TranslatableInterface $id
     * @param array|int|float|string $options
     * @param string $domain
     * @param string|null $locale
     *
     * @return string
     */
    public function _(string|Stringable|TranslatableInterface $id, array|int|float|string $options = [], string $domain = 'messages', ?string $locale = null): string
    {
        if ($id instanceof TranslatableInterface) {
            return $id->trans($this->translator());
        }

        if (!is_array($options)) {
            $options = [$options];
        }

        $translated = $this->translator()->trans($id, $options, $domain, $locale);

        if (array_is_list($options)) {
            $translated = vsprintf($translated, $options);
        }

        return $translated;
    }

    public function translate(string|Stringable|TranslatableInterface $id, array|int|float|string $options = [], string $domain = 'messages', ?string $locale = null): string
    {
        return $this->_($id, $options, $domain, $locale);
    }

    /**
     * @param string|Stringable|TranslatableInterface $id
     * @param int $number
     * @param array|int|float|string $options
     * @param string $domain
     * @param string|null $locale
     *
     * @return string
     */
    public function _n(string|Stringable|TranslatableInterface $id, int $number, array|int|float|string $options = [], string $domain = 'messages', ?string $locale = null): string
    {
        if ($id instanceof TranslatableInterface) {
            return $id->trans($this->translator());
        }

        if (!is_array($options)) {
            $options = [$options];
        }

        $translated = $this->translator()->trans($id, $options + ['%count%' => $number], $domain, $locale);

        if (array_is_list($options)) {
            $translated = vsprintf($translated, $options);
        }

        return $translated;
    }

    public function translatePlural(string|Stringable|TranslatableInterface $id, int $number, array|int|float|string $options = [], string $domain = 'messages', ?string $locale = null): string
    {
        return $this->_n($id, $number, $options, $domain, $locale);
    }

    private function translator(): TranslatorInterface
    {
        if ($this->translator instanceof Closure) {
            $this->translator = ($this->translator)();
        }

        return $this->translator;
    }
}

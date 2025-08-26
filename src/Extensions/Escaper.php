<?php

namespace Bdf\Templating\Extensions;

use Laminas\Escaper\EscaperInterface;
use Psr\Container\ContainerInterface;

trait Escaper
{
    /**
     * The internal escaper
     */
    private EscaperInterface $internalEscaper;

    /**
     * Alias of escape
     *
     * @param mixed  $value
     *
     * @return string
     */
    public function e(mixed $value): string
    {
        return $this->escape($value, 'html');
    }

    /**
     * Alias of escapeAttr
     *
     * @param mixed  $value
     *
     * @return string
     */
    public function eAttr(mixed $value): string
    {
        return $this->escape($value, 'attr');
    }

    /**
     * Escape for HTML attr
     *
     * @param mixed $value
     *
     * @return string
     */
    public function escapeAttr(mixed $value): string
    {
        return $this->escape($value, 'attr');
    }

    /**
     * Alias of escapeUrl
     *
     * @param string $value
     *
     * @return string
     */
    public function eUrl(mixed $value): string
    {
        return $this->escape($value, 'url');
    }

    /**
     * Encode URL
     *
     * @param string $value
     *
     * @return string
     */
    public function escapeUrl(string $value): string
    {
        return $this->escape($value, 'url');
    }

    /**
     * Alias of escapeJs
     *
     * @param mixed $value
     *
     * @return string
     */
    public function eJs(mixed $value): string
    {
        return $this->escape($value, 'js');
    }

    /**
     * Escape for JS
     *
     * @param mixed $value
     *
     * @return string
     */
    public function escapeJs(mixed $value): string
    {
        return $this->escape($value, 'js');
    }

    /**
     * Alias of escapeCss
     *
     * @param mixed $value
     *
     * @return string
     */
    public function eCss(mixed $value): string
    {
        return $this->escape($value, 'css');
    }

    /**
     * Escape for CSS
     *
     * @param mixed $value
     *
     * @return string
     */
    public function escapeCss(mixed $value): string
    {
        return $this->escape($value, 'css');
    }

    /**
     * Escape char for a given context.
     * The context could be html, js, attr, css, url
     *
     * @param mixed $value
     * @param string $context
     *
     * @return mixed
     */
    public function escape(mixed $value, string $context = 'html'): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $this->internalEscaper ??= new \Laminas\Escaper\Escaper($this->resolveCharset());

        return match ($context) {
            'attr' => $this->internalEscaper->escapeHtmlAttr($value),
            'js' => $this->internalEscaper->escapeJs($value),
            'css' => $this->internalEscaper->escapeCss($value),
            'url' => $this->internalEscaper->escapeUrl($value),
            default => $this->internalEscaper->escapeHtml($value), // html is the default
        };
    }

    /**
     * Set the internal escaper
     */
    public function setInternalEscaper(EscaperInterface $escaper): void
    {
        $this->internalEscaper = $escaper;
    }

    /**
     * Convert string from charset to another
     *
     * @param string $value
     * @param string $to
     * @param string $from
     *
     * @return string
     */
    public function convertEncoding(string $value, string $to, string $from): string
    {
        return mb_convert_encoding($value, $to, $from);
    }

    /**
     * Try to resolve the charset from the di container is defined or from mb_internal_encoding
     *
     * @return string
     * @internal
     */
    private function resolveCharset(): string
    {
        if (isset($this->di) && $this->di instanceof ContainerInterface && $this->di->has('charset')) {
            return (string) $this->di->get('charset');
        }

        return mb_internal_encoding() ?: 'UTF-8';
    }
}

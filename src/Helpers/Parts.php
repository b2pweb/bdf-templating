<?php

namespace Bdf\Templating\Helpers;

use function in_array;

/**
 * @package Bdf\Templating\Helpers
 */
class Parts implements HelperInterface
{
    /**
     * @var list<string>
     */
    protected array $parts = [];

    /**
     * @var array<string, string>
     */
    protected array $openParts = [];

    /**
     * @see HelperInterface::getName
     */
    public function getName(): string
    {
        return 'parts';
    }

    /**
     * @param string $name
     */
    public function open(string $name): void
    {
        if (in_array($name, $this->openParts, true)) {
            throw new \Exception('Part "' . $name . '" already opened.');
        }

        $this->openParts[] = $name;
        $this->parts[$name] = '';

        ob_start();
        ob_implicit_flush(0);
    }

    /**
     *
     */
    public function close(): void
    {
        if (!$this->openParts) {
            throw new \Exception('No parts opened.');
        }

        $name = array_pop($this->openParts);

        $this->parts[$name] = ob_get_clean();
    }

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function has(string $name): bool
    {
        return isset($this->parts[$name]);
    }

    /**
     * @param string         $name
     * @param boolean|string $default
     *
     * @return string|bool
     */
    public function get(string $name, string|bool|null $default = false): string|bool|null
    {
        return $this->parts[$name] ?? $default;
    }

    /**
     * @param string $name
     * @param string|null $content
     */
    public function set(string $name, string|null $content): void
    {
        $this->parts[$name] = $content;
    }

    /**
     * @param string         $name
     * @param boolean|string $default
     *
     * @return boolean
     */
    public function output(string $name = '_content', string|bool $default = false): bool
    {
        if (!isset($this->parts[$name])) {
            if (false !== $default) {
                echo $default;

                return true;
            }

            return false;
        }

        echo $this->parts[$name];

        return true;
    }
}

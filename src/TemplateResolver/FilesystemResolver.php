<?php

namespace Bdf\Templating\TemplateResolver;

use Bdf\Templating\Exception\InvalidArgumentException;

use function is_readable;
use function sprintf;

/**
 * @final
 */
class FilesystemResolver implements TemplateResolverInterface
{
    /**
     * @var list<string>
     */
    protected array $paths;

    /**
     * @param list<string>|string $paths
     */
    public function __construct(array|string $paths)
    {
        $this->setPaths($paths);
    }

    /**
     * @param list<string>|string $paths
     * @internal
     */
    public function setPaths(array|string $paths): void
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }

        $this->paths = [];

        foreach ($paths as $path) {
            $this->paths[] = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($template): string
    {
        foreach ($this->paths as $path) {
            $file = $path . $template;

            if (is_readable($file)) {
                return $file;
            }
        }

        throw new InvalidArgumentException(sprintf('The template "%s" does not exist', $template));
    }
}

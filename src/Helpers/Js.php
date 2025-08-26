<?php

namespace Bdf\Templating\Helpers;

use Bdf\Templating\Asset\AssetInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Asset\Exception\InvalidArgumentException;
use Symfony\Component\Asset\Packages;

/**
 *
 */
class Js implements HelperInterface
{
    /**
     * Contient la liste des tag script
     * @var array<string>
     */
    private array $js = [];

    /**
     * @var ContainerInterface|null
     * @deprecated Use constructor injection instead.
     */
    protected ?ContainerInterface $di = null;

    public function __construct(
        private readonly ?Packages $assets = null,
    ) {}

    /**
     * @deprecated Use constructor injection instead.
     */
    public function setDI(ContainerInterface $di): void
    {
        $this->di = $di;
    }

    /**
     * @see HelperInterface::getName
     */
    public function getName(): string
    {
        return 'js';
    }

    /**
     * Ajoute un script en début de tableau
     * 
     * @param string|array $files
     * @param array $attributes
     * @param string|bool $conditional
     *
     * @return $this
     */
    public function prepend(string|array $files, array $attributes = [], string|bool $conditional = false): self
    {
        if (!is_array($files)) {
            $files = [$files];
        }
        
        $merge = [];

        foreach ($files as $file) {
            $attributes['src'] = $this->assetUrl($file);
            $merge[]           = $this->addConditionalComment($this->renderTag($attributes), $conditional);
        }
        
        $this->js = array_merge($merge, $this->js);
        
        return $this;
    }

    /**
     * Bufferise un script à partir d'un fichier
     * 
     * @param string $file
     * @param array $options
     * @param bool $conditional
     *
     * @return $this
     */
    public function append(string $file, array $options = [], string|bool $conditional = false): self
    {
        $this->js[] = $this->link($file, $options, $conditional);
        
        return $this;
    }

    /**
     * Bufferise un script
     * 
     * @param string $data
     * @param bool|string $conditional
     *
     * @return $this
     */
    public function appendTag(string $data, string|bool $conditional = false): self
    {
        $this->js[] = $this->tag($data, $conditional);
        
        return $this;
    }

    /**
     * Ajoute un script à partir d'un fichier
     * 
     * @param string $file
     * @param array $options
     * @param string|bool $conditional
     *
     * @return string
     */
    public function link(string $file, array $options = [], string|bool $conditional = false): string
    {
        $options['src'] = $this->assetUrl($file);
        
        return $this->addConditionalComment($this->renderTag($options), $conditional);
    }

    /**
     * Créer un script javascript
     * 
     * @param string $data
     * @param string|bool $conditional
     *
     * @return string
     */
    public function tag(string $data, string|bool $conditional = false): string
    {
        return $this->addConditionalComment('<script>' . $data . '</script>', $conditional);
    }

    /**
     * Rendu d'une balise script
     * 
     * @param array $attributes
     *
     * @return string
     */
    public function renderTag(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }
        
        $tag = '<script ';
        foreach ($attributes as $key => $value) {
            $tag .= $key . '="' . $value . '" ';
        }
        $tag .= '></script>';

        return $tag;
    }

    /**
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return empty($this->js);
    }

    /**
     * Affiche les script generes
     *
     * @return string
     */
    public function render(): string
    {
        return $this->toString();
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return implode('', $this->js);
    }

    /**
     * Ajoute une condition xhtml
     * 
     * @param string $js
     * @param string|bool $conditional
     *
     * @return string
     */
    protected function addConditionalComment(string $js, string|bool $conditional): string
    {
        if (empty($conditional)) {
            return $js;
        }

        return "<!--[if " . $conditional . "]>" . $js . "<![endif]-->";
    }

    private function assetUrl(string $file): string
    {
        if ($this->assets !== null) {
            try {
                return $this->assets->getUrl($file, 'js');
            } catch (InvalidArgumentException) {
                return $this->assets->getUrl($file);
            }
        }

        // Fallback to legacy behavior using the DI container
        assert($this->di !== null);
        $asset = $this->di->has('asset-js') ? $this->di->get('asset-js') : $this->di->get('asset');
        assert($asset instanceof AssetInterface);

        return $asset->getUrl($file);
    }
}

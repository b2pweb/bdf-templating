<?php

namespace Bdf\Templating\Helpers;

use Bdf\Templating\Asset\AssetInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Asset\Exception\InvalidArgumentException;
use Symfony\Component\Asset\Packages;

/**
 *
 */
class Css implements HelperInterface
{
    /**
     * CSS Tags list
     *
     * @var array
     */
    private array $css = [];

    /**
     * @var ContainerInterface|null
     * @deprecated Use constructor injection instead.
     */
    protected ?ContainerInterface $di = null;

    public function __construct(
        private readonly ?Packages $assets = null,
    ) {}

    /**
     * @see HelperInterface::getName
     */
    public function getName(): string
    {
        return 'css';
    }

    /**
     * @deprecated Use constructor injection instead.
     */
    public function setDI(ContainerInterface $di): void
    {
        $this->di = $di;
    }

    /**
     * Ajoute une feuille de style en debut de tableau
     * 
     * @param string|array $files
     * @param array $options
     * @param bool|string $conditional
     *
     * @return $this
     */
    public function prepend(string|array $files, array $options = [], bool|string $conditional = false): self
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        $merge = [];

        foreach ($files as $file) {
            $options['href'] = $this->assetUrl($file);
            $merge[]         = $this->addConditionalComment($this->renderTag($options), $conditional);
        }

        $this->css = array_merge($merge, $this->css);
        
        return $this;
    }

    /**
     * Bufferise une feuille de style à partir d'un fichier
     * 
     * @param string $file
     * @param array $options
     * @param string|bool $conditional
     *
     * @return $this
     */
    public function add(string $file, array $options = [], string|bool $conditional = false): self
    {
        $this->css[] = $this->link($file, $options, $conditional);
        
        return $this;
    }

    /**
     * Bufferise une feuille de style
     * 
     * @param string $data
     * @param string|bool $conditional
     *
     * @return $this
     */
    public function addTag(string $data, string|bool $conditional = false): self
    {
        $this->css[] = $this->tag($data, $conditional);
        
        return $this;
    }

    /**
     * Ajoute une feuille de style à partir d'un fichier
     * 
     * @param string $file
     * @param array $options
     * @param string|bool $conditional
     * 
     * @return string
     */
    public function link(string $file, array $options = [], string|bool $conditional = false): string
    {
        $options['href'] = $this->assetUrl($file);
        
        return $this->addConditionalComment($this->renderTag($options), $conditional);
    }

    /**
     * Créer une feuille de style
     * 
     * @param string $data
     * @param string|bool $conditional
     *
     * @return string
     */
    public function tag(string $data, string|bool $conditional = false): string
    {
        return $this->addConditionalComment('<style type="text/css">' . $data . '</style>', $conditional);
    }

    /**
     * Format une balise
     * 
     * @param array $options
     *
     * @return string
     */
    public function renderTag(array $options): string
    {
        if (empty($options)) {
            return '';
        }

        if (!isset($options['type'])) {
            $options['type'] = 'text/css';
        }
        
        if (!isset($options['rel'])) {
            $options['rel'] = 'stylesheet';
        }
        
        $stylesheet = '';
        
        foreach ($options as $key => $value) {
            $stylesheet .= $key . '="' . $value . '" ';
        }

        return '<link ' . $stylesheet . '/>';
    }

    /**
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return empty($this->css);
    }

    /**
     * Affiche les css generes
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
        return implode('', $this->css);
    }

    /**
     * Ajoute une condition xhtml
     * 
     * @param string $css
     * @param string|bool $conditional
     *
     * @return string
     */
    protected function addConditionalComment(string $css, string|bool $conditional): string
    {
        if (empty($conditional)) {
            return $css;
        }
        
        return "<!--[if " . $conditional . "]>" . $css . "<![endif]-->";
    }

    private function assetUrl(string $file): string
    {
        if ($this->assets !== null) {
            try {
                return $this->assets->getUrl($file, 'css');
            } catch (InvalidArgumentException) {
                return $this->assets->getUrl($file);
            }
        }

        // Fallback to legacy behavior using the DI container
        assert($this->di !== null);
        $asset = $this->di->has('asset-css') ? $this->di->get('asset-css') : $this->di->get('asset');
        assert($asset instanceof AssetInterface);

        return $asset->getUrl($file);
    }
}

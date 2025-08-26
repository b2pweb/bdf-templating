<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bdf\Templating\Asset;

/**
 * The basic package will add a version to asset URLs.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 * @deprecated use symfony/asset
 */
class AbstractAsset implements AssetInterface
{
    /**
     * @var string
     */
    private $version;
    
    /**
     * @var string
     */
    private $format;

    /**
     * Constructor.
     *
     * @param string $version  Package cache key.
     * @param string $format   The format used to apply the version
     */
    public function __construct($version = -1, $format = null)
    {
        $this->version = $this->formatVersion($version);
        $this->format  = $format ?: '%s?%s';
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($path, $version = null)
    {
        if (false !== strpos($path, '://') || 0 === strpos($path, '//')) {
            return $path;
        }

        return $this->applyVersion($path, $version);
    }

    /**
     * Applies version to the supplied path.
     *
     * @param string           $path     A path
     * @param string|bool|null $version  A specific version
     *
     * @return string The versionized path
     */
    protected function applyVersion($path, $version = null)
    {
        $version = null !== $version ? $this->formatVersion($version) : $this->version;
        
        if (null === $version) {
            return $path;
        }

        $versionized = sprintf($this->format, ltrim($path, '/'), $version);

        if ($path && '/' === $path[0]) {
            $versionized = '/' . $versionized;
        }

        return $versionized;
    }
    
    /**
     * Format version
     *   -1            deactivate version (set to null)
     *   false|0       set time
     *   string        set version number
     * 
     * @param string $version
     * @return string
     */
    protected function formatVersion($version)
    {
        $version = $version ?: time();
        
        if ((int)$version === -1) {
            $version = null;
        }
        
        return $version;
    }
}

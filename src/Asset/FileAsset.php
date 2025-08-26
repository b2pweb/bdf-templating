<?php

namespace Bdf\Templating\Asset;

/**
 * The path packages adds a version and a base path to asset URLs.
 *
 * @author seb
 * @deprecated use symfony/asset
 */
class FileAsset implements AssetInterface
{
    /**
     * @var string[]
     */
    private $paths = [];

    /**
     * Constructor.
     *
     * @param array $paths
     */
    public function __construct(array $paths = [])
    {
        foreach ($paths as $path) {
            $this->paths[] = rtrim($path, '/') . '/';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($file, $version = null)
    {
        foreach ($this->paths as $path) {
            if (file_exists($path . $file)) {
                return $path . $file;
            }
        }
        
        return $file;
    }

    /**
     * Returns defined paths.
     *
     * @return string[]
     */
    public function getPaths()
    {
        return $this->paths;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return null;
    }
}

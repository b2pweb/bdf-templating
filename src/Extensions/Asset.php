<?php

namespace Bdf\Templating\Extensions;

use Bdf\Templating\Asset\AssetInterface;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\Packages;

use function assert;


/**
 * Necessite l'acces à la variable $di (@see DIAccesorInterface)
 * 
 * configuration di requise:
 *    - asset => Bdf\Templating\Asset\AssetInterface
 *
 * @deprecated Use symfony/asset and inject {@see Packages} instead.
 */
trait Asset
{
    /**
     * Returns asset manager
     * 
     * @param string  $assetName
     * @param boolean $fallbackToDefault
     *
     * @return AssetInterface|PackageInterface
     * @deprecated use symfony/asset and inject {@see Packages} instead.
     */
    public function asset($assetName = null, $fallbackToDefault = true)
    {
        if ($this->di->has('bdf_templating.assets')) {
            $packages = $this->di->get('bdf_templating.assets');
            assert($packages instanceof Packages);

            try {
                return $packages->getPackage($assetName);
            } catch (\InvalidArgumentException) {
                return $packages->getPackage();
            }
        }

        if ($assetName !== null && $fallbackToDefault && ! $this->di->has("asset-{$assetName}")) {
            $assetName = null;
        }
        
        return $this->di->get('asset' . ($assetName ? '-' . $assetName : ''));
    }
}

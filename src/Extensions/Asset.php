<?php

namespace Bdf\Templating\Extensions;

use Bdf\Templating\Asset\AssetInterface;
use Symfony\Component\Asset\Packages;


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
     * @return AssetInterface
     * @deprecated use symfony/asset and inject {@see Packages} instead.
     */
    public function asset($assetName = null, $fallbackToDefault = true)
    {
        if ($assetName !== null && $fallbackToDefault && ! $this->di->has("asset-{$assetName}")) {
            $assetName = null;
        }
        
        return $this->di->get('asset' . ($assetName ? '-' . $assetName : ''));
    }
}

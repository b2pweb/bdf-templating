<?php

namespace Bdf\Templating\Asset;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_Asset
 */
class FileAssetTest extends TestCase
{
    /**
     * 
     */
    public function test_default_values()
    {
        $asset = new FileAsset();
        
        $this->assertEquals([], $asset->getPaths());
        $this->assertEquals(null, $asset->getVersion());
    }
    
    /**
     * 
     */
    public function test_injected_values()
    {
        $asset = new FileAsset(['dir']);
        
        $this->assertEquals(['dir/'], $asset->getPaths());
    }
    
    /**
     * 
     */
    public function test_getUrl_add_path_if_exists()
    {
        $asset = new FileAsset([__DIR__]);
        
        $this->assertEquals(__DIR__.'/FileAssetTest.php', $asset->getUrl('FileAssetTest.php'));
    }
    
    /**
     * 
     */
    public function test_getUrl_dont_add_path_if_not_exists()
    {
        $asset = new FileAsset([__DIR__]);
        
        $this->assertEquals('unknow', $asset->getUrl('unknow'));
    }
}

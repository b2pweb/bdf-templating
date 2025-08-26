<?php

namespace Bdf\Templating\Asset;

use PHPUnit\Framework\TestCase;

/**
 * @group Bdf_Templating
 * @group Bdf_Templating_Asset
 */
class PathAssetTest extends TestCase
{
    /**
     * 
     */
    public function test_default_values()
    {
        $asset = new PathAsset();
        
        $this->assertEquals('/', $asset->getBasePath());
        $this->assertEquals(null, $asset->getVersion());
    }
    
    /**
     * 
     */
    public function test_injected_values()
    {
        $asset = new PathAsset('/test/', '1.0');
        
        $this->assertEquals('/test/', $asset->getBasePath());
        $this->assertEquals('1.0', $asset->getVersion());
    }
    
    /**
     * 
     */
    public function test_format_base_path()
    {
        $this->assertEquals('/test/', (new PathAsset('test'))->getBasePath(), 'first assert');
        $this->assertEquals('/test/', (new PathAsset('/test'))->getBasePath(), 'second assert');
        $this->assertEquals('/test/', (new PathAsset('/test/'))->getBasePath(), 'third assert');
    }
    
    /**
     * 
     */
    public function test_getUrl_default()
    {
        $asset = new PathAsset();
        
        $this->assertEquals('/style.css', $asset->getUrl('style.css'));
    }
    
    /**
     * 
     */
    public function test_getUrl_add_base_path()
    {
        $asset = new PathAsset('/test');
        
        $this->assertEquals('/test/style.css', $asset->getUrl('style.css'));
    }
    
    /**
     * 
     */
    public function test_getUrl_add_version()
    {
        $asset = new PathAsset(null, '1.0');
        
        $this->assertEquals('/style.css?1.0', $asset->getUrl('style.css'));
    }
    
    /**
     * 
     */
    public function test_getUrl_add_version_in_parameter()
    {
        $asset = new PathAsset();
        
        $this->assertEquals('/style.css?1.0', $asset->getUrl('style.css', '1.0'));
    }
    
    /**
     * 
     */
    public function test_getUrl_add_version_with_custom_format()
    {
        $asset = new PathAsset(null, '1.0', '%s__%s');
        
        $this->assertEquals('/style.css__1.0', $asset->getUrl('style.css'));
    }
    
    /**
     * 
     */
    public function test_getUrl_dont_add_base_url_if_exists()
    {
        $asset = new PathAsset('/test');
        
        $this->assertEquals('/style.css', $asset->getUrl('/style.css'));
    }
    
    /**
     * 
     */
    public function test_getUrl_dont_change_absolute_path()
    {
        $asset = new PathAsset('/test', '1.0');
        
        $this->assertEquals('http://style.css', $asset->getUrl('http://style.css'));
    }
    
    /**
     * 
     */
    public function test_getUrl_set_time_by_constructor()
    {
        $asset = new PathAsset(null, false);
        
        $time1 = time();
        $time2 = $time1;
        $time3 = $time2;
        
        $this->assertThat($asset->getUrl('style.css'), $this->logicalOr(
            '/style.css?' . $time1,
            '/style.css?' . $time2,
            '/style.css?' . $time3
        ));
    }
    
    /**
     * 
     */
    public function test_getUrl_set_time_by_parameter()
    {
        $asset = new PathAsset(null, '1.0');
        
        $time1 = time();
        $time2 = $time1;
        $time3 = $time2;
        
        $this->assertThat($asset->getUrl('style.css', false), $this->logicalOr(
            '/style.css?' . $time1,
            '/style.css?' . $time2,
            '/style.css?' . $time3
        ));
    }
}

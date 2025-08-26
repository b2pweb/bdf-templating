<?php

namespace Bdf\Templating\Helpers;

/**
 * B2pJs
 */
class B2pJs implements HelperInterface
{
    use \Bdf\Templating\Helpers\EngineAccessor;

    /**
     * Depot de modules
     * 
     * @var string[]
     */
    protected array $modulePaths = [];
    
    /**
     * @var array 
     */
    protected array $configs = [];

    /**
     * @var array 
     */
    protected array $modules = [];

    /**
     * @var string 
     */
    private string $buffer = '';

    /**
     * @var list<string>
     */
    protected array $onModulesReady = [];

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'b2pJs';
    }

    /**
     * @param string $path
     */
    public function registerPath($path): void
    {
        $this->modulePaths[] = $path;
    }

    /**
     * @param string $module
     * @param mixed $config
     * @param string $callback
     */
    public function useModule($module, $config = null, $callback = null): void
    {
        $this->config($module, $config);
        $this->modules($module, $callback);
    }

    /**
     * @param string $module
     * @param mixed $config
     */
    public function importModule($module, $config = null): void
    {
        $this->config($module, $config);
        
        $this->doImportModule($module);
    }

    /**
     * @todo voir comment merger la config si le module est deja ajouté
     * 
     * @param string|array $module
     * @param mixed $config
     */
    public function config(string|array $module, mixed $config = null): void
    {
        if (is_array($config)) {
            $this->configs[$module] = $config;

            return;
        }

        if (is_array($module)) {
            $this->configs = array_merge($this->configs, $module);

            return;
        }

        if (null === $config) {
            return;
        }

        $this->configs[$module] = $config;
    }

    /**
     * @param string|array $modules
     * @param string $callback
     */
    public function modules(string|array $modules, ?string $callback = null): void
    {
        if ($callback) {
            $this->onModulesReady[] = $callback;
        }

        if (!is_array($modules)) {
            $modules = [$modules];
        }

        $this->modules = array_merge($this->modules, $modules);
    }

    /**
     * 
     */
    public function importJs(): void
    {
        $output  = '';
        $output .= $this->renderConfigs();
        $output .= $this->renderUse();
        $output .= $this->buffer;

        $this->onModulesReady = $this->modules = $this->configs = [];

        if ($output) {
            $this->helper(Js::class)->appendTag($output);
        }
    }

    /**
     *
     */
    protected function renderConfigs(): string
    {
        if (!$this->configs) {
            return '';
        }

        $config = $this->configs;
        $toReplace = $this->getClosureKey($config);

        return sprintf('B2p.Config.merge(%s);', strtr(json_encode($config), $toReplace));
    }

    /**
     * Parcours du traversable pour identifier les closures js
     *
     * @param  array  $object
     * @param  string $prefix
     *
     * @return array
     */
    protected function getClosureKey(array &$object, string $prefix = ''): array
    {
        $toReplace = [];

        foreach ($object as $key => &$value) {
            if (is_array($value)) {
                $key = str_replace('/', '_', $key);
                $toReplace = array_merge($toReplace, $this->getClosureKey($value, $prefix.$key.'.'));
                continue;
            }

            if (is_string($value) && str_starts_with($value, 'function(')) {
                $toReplace['"{'.$prefix.$key.'}"'] = $value;
                $value = '{'.$prefix.$key.'}';
            }

        }

        return $toReplace;
    }

    /**
     * 
     */
    protected function renderUse(): string
    {
        if (!$this->modules) {
            return '';
        }
        
        $callback = '';

        if ($this->onModulesReady) {
            $callback = ', function(){' . implode('', $this->onModulesReady) . '}';
        }

        return sprintf(
            'B2p.Modules.use(["%s"]%s);', implode('","', $this->modules), $callback
        );
    }
    
    /**
     * 
     */
    protected function doImportModule(string $module):  void
    {
        $filename = null;
        
        foreach ($this->modulePaths as $path) {
            $filename = sprintf($path, $module);
            
            if (file_exists($path)) {
                break;
            }
        }
        
        if (empty($filename)) {
            throw new \LogicException('Unknow js module "' . $module . '". Check your path configuration');
        }
        
        $this->buffer .= file_get_contents($filename);
    }
}

<?php

namespace Bdf\Templating\Bundle\DependencyInjection;

use Bdf\Templating\EngineInterface;
use Bdf\Templating\Helpers\HelperInterface;
use Bdf\Templating\Test\TestEngine;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class BdfTemplatingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('bdf_templating.php');

        $container
            ->registerForAutoconfiguration(HelperInterface::class)
            ->setPublic(true)
            ->setShared(false)
        ;

        $container->setParameter('bdf.templating.helper_namespaces', $config['helper_namespaces']);
        $container->setParameter('bdf.templating.view_default_layout', $config['default_layout']);
        $container->setParameter('bdf.templating.template_directory', $config['template_directory']);

        if ($config['test']) {
            $container
                ->setAlias(EngineInterface::class, TestEngine::class)
                ->setPublic(true)
            ;
        }
    }
}

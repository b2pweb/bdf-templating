<?php

namespace Bdf\Templating\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('bdf_templating');

        $node = $treeBuilder->getRootNode();
        $node
            ->children()
                ->arrayNode('helper_namespaces')
                    ->prototype('scalar')->end()
                    ->defaultValue([])
                ->end()
                ->scalarNode('default_layout')->defaultValue('base')->end()
                ->scalarNode('template_directory')->defaultValue('%kernel.project_dir%/templates')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

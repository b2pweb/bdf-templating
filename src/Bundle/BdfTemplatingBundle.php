<?php

namespace Bdf\Templating\Bundle;

use Override;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BdfTemplatingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new class implements CompilerPassInterface {
            #[Override]
            public function process(ContainerBuilder $container): void
            {
                if ($container->has('assets.packages')) {
                    $container
                        ->setAlias('bdf_templating.assets', 'assets.packages')
                        ->setPublic(true)
                    ;
                }
            }
        });
    }
}

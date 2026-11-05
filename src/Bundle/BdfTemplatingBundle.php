<?php

namespace Bdf\Templating\Bundle;

use Bdf\Templating\Helpers\HelperInterface;
use Bdf\Templating\PhpEngine;
use Override;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Throwable;

use function is_subclass_of;

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

        $container->addCompilerPass(new class implements CompilerPassInterface {
            #[Override]
            public function process(ContainerBuilder $container): void
            {
                $engine = $container->findDefinition(PhpEngine::class);

                foreach ($container->getDefinitions() as $id => $definition) {
                    $class = $definition->getClass();

                    if ($class !== null && is_subclass_of($class, HelperInterface::class)) {
                        if ($id !== $class && !$container->has($class)) {
                            $container->setAlias($class, $id)->setPublic(true);
                        }

                        try {
                            $helperName = (new ReflectionClass($class))->newInstanceWithoutConstructor()->getName();
                            $engine->addMethodCall('declareHelper', [$helperName, $class]);
                        } catch (Throwable) {
                            // Ignore
                        }
                    }
                }
            }
        });
    }
}

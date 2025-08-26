<?php

namespace Bdf\Templating\_files;

use Bdf\Templating\Bundle\BdfTemplatingBundle;
use Bdf\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Attribute\Route;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    private string $cacheDir;

    public function __construct(
        /** @var list<\Closure(ContainerConfigurator):void> */
        private array $configurators = [],
    ) {
        parent::__construct('test', true);

        $this->cacheDir = sys_get_temp_dir().'/bdf_templating/'.bin2hex(random_bytes(8));
    }

    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new BdfTemplatingBundle();
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'S0ME_SECRET'
        ]);

        $container->extension('bdf_templating', [
            'template_directory' => __DIR__.'/templates',
            'default_layout' => 'base',
            'helper_namespaces' => [
                'Bdf\\Templating\\Helpers'
            ]
        ]);

        foreach ($this->configurators as $configurator) {
            $configurator($container);
        }
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(EngineInterface $engine): Response
    {
        return new Response($engine->render('basic', ['name' => 'World']));
    }

    #[Route('/string', methods: ['GET'])]
    public function string(EngineInterface $engine): string
    {
        return $engine->render('basic', ['name' => 'World']);
    }
}

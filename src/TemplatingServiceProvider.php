<?php

namespace Bdf\Templating;

use Bdf\Templating\Asset\PathAsset;
use Bdf\Templating\TemplateResolver\FilesystemResolver;
use Bdf\Web\Application;
use Bdf\Web\Extensions as BaseExtensions;
use Bdf\Web\Providers\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Fragment\InlineFragmentRenderer;

/**
 * Configuration du templating engine
 * 
 * Configurable par le DI. Nécessite
 *   view                   = (object) view engine. Defaut PhpEngine
 *   helperNamespaces       = (string[]) tableau de namespace pour configurer le chargement des helpers
 *   viewFilters            = (object[]) filters
 *   viewDefaultLayout      = (string) nom du layout par defaut
 *   templateResolver       = (object) 
 *   templateDirectory      = (string) 
 *   fragmentHandler        = (object) 
 *   inlineFragmentRenderer = (object) 
 *   asset                  = (object) gestion des assets. Utillise la config 'app.cacheKey' pour la gestion de la version.
 *   assetPath              = (string[]) path des assets. Ne pas preciser le base path, celui ci sera ajouté automatiquement
 */
class TemplatingServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(Application $app)
    {
        $app->set('view', function($app) {
            $view = new PhpEngine(
                $app,
                $app->get('templateResolver'),
                static fn () => $app->get('fragmentHandler'),
                [
                    // Add extensions used by the legacy bdf-templating library
                    new class ($app) {
                        use BaseExtensions\Auth;
                        use BaseExtensions\Routing;
                        use BaseExtensions\Session;
                        use BaseExtensions\Translation;

                        public function __construct(
                            protected ContainerInterface $di,
                        ) {}
                    },
                ],
            );

            if ($app->has('helperNamespaces')) {
                $view->setHelperNamespaces($app->get('helperNamespaces'));
            }

            if ($app->has('viewFilters')) {
                $view->setFilters($app->get('viewFilters'));
            }

            if ($app->has('viewDefaultLayout')) {
                $view->setDefaultLayout($app->get('viewDefaultLayout'));
            }

            return $view;
        });

        $app->set('templateResolver', function($app) {
            return new FilesystemResolver(
                $app->get('templateDirectory')
            );
        });

        $app->set('fragmentHandler', function($app) {
            return new FragmentHandler(
                $app->get('requestStack'),
                [$app->get('inlineFragmentRenderer')]
            );
        });

        $app->set('inlineFragmentRenderer', function($app) {
            return new InlineFragmentRenderer($app, $app->eventDispatcher());
        });

        // Should it be in the view resolver ?
        $app->set('asset', function($app) {
            $request = $app->get('requestStack')->getCurrentRequest();
            $path    = $app->has('assetPath') ? $app->get('assetPath') : '/assets/';
            
            return new PathAsset($request->getBasePath().$path, $app->config('app.cacheKey'));
        });
    }
}

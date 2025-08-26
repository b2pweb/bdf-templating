<?php

namespace Bdf\Templating;

use Bdf\Templating\Listeners\ViewListener;
use Bdf\Web\Application;
use Bdf\Web\Providers\BootableProviderInterface;
use Bdf\Web\Providers\ServiceProviderInterface;

/**
 * Configuration de la gestion du format.
 * 
 * Configurable par le DI uniquement. Nécessite
 *   viewListener    = (object) listener configurant la vue et la réponse en fonction du format
 *   viewListener-headers    = (array) Liste des headers par format
 *   viewListener-suffixes   = (array) suffixe par format
 *   viewListener-layouts    = (array) layout par format
 */
class ViewResolverServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(Application $app)
    {
        $app->set('viewListener', function($app) {
            $listener = new ViewListener($app->get('view'), $app->get('charset'));
            
            if ($app->has('viewListener-headers')) {
                $listener->setHeaders($app->get('viewListener-headers'));
            }
            
            if ($app->has('viewListener-suffixes')) {
                $listener->setSuffixes($app->get('viewListener-suffixes'));
            }
            
            if ($app->has('viewListener-layouts')) {
                $listener->setLayouts($app->get('viewListener-layouts'));
            }
            
            return $listener;
        });
    }
    
    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $app->subscribe($app->get('viewListener'));
    }
}

<?php

namespace Bdf\Templating;

use Bdf\Templating\Listeners\LazyViewListener;
use Bdf\Web\Application;

/**
 * View resolver provider using lazy
 */
class LazyViewResolverServiceProvider extends ViewResolverServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $app->subscribe(new LazyViewListener($app));
    }
}
<?php

use Bdf\Templating\ConfigurableViewInterface;
use Bdf\Templating\EngineInterface;
use Bdf\Templating\Extensions\Session;
use Bdf\Templating\Extensions\Translation;
use Bdf\Templating\Extensions\Url;
use Bdf\Templating\Helpers\B2pJs;
use Bdf\Templating\Helpers\Css;
use Bdf\Templating\Helpers\Js;
use Bdf\Templating\Helpers\Parts;
use Bdf\Templating\PhpEngine;
use Bdf\Templating\TemplateResolver\FilesystemResolver;
use Bdf\Templating\TemplateResolver\TemplateResolverInterface;
use Bdf\Templating\Test\TestEngine;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_closure;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $parameters = $container->parameters();
    $parameters
        ->set('bdf.templating.helper_namespaces', [])
        ->set('bdf.templating.view_default_layout', 'base')
        ->set('bdf.templating.template_directory', '%kernel.project_dir%/templates')
    ;

    $services = $container->services();

    $services
        ->set(PhpEngine::class)
        ->args([
            service('service_container'),
            service(TemplateResolverInterface::class),
            service_closure('fragment.handler')->nullOnInvalid(),
            tagged_iterator('bdf.templating.extension'),
        ])
        ->call('setHelperNamespaces', [param('bdf.templating.helper_namespaces')])
        ->call('setDefaultLayout', [param('bdf.templating.view_default_layout')])
    ;

    $services
        ->set(TestEngine::class)
        ->args([service(PhpEngine::class)])
        ->public()
    ;

    $services->set(FilesystemResolver::class)->args([param('bdf.templating.template_directory')]);

    $services->alias(EngineInterface::class, PhpEngine::class)->public();
    $services->alias(ConfigurableViewInterface::class, PhpEngine::class)->public();
    $services->alias(TemplateResolverInterface::class, FilesystemResolver::class);

    $services->set(Translation::class)->args([service_closure(TranslatorInterface::class)])->tag('bdf.templating.extension');
    $services->set(Session::class)->args([service('request_stack')])->tag('bdf.templating.extension');
    $services
        ->set(Url::class)
        ->args([
            service(UrlGeneratorInterface::class),
            service('request_stack'),
        ])
        ->tag('bdf.templating.extension')
    ;

    $services->set(B2pJs::class)->autowire()->autoconfigure();
    $services->set(Css::class)->autowire()->autoconfigure();
    $services->set(Js::class)->autowire()->autoconfigure();
    $services->set(Parts::class)->autowire()->autoconfigure();
};

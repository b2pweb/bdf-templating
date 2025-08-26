<?php

namespace Bdf\Templating\Listeners;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;

/**
 * Lazy listener for view listener
 *
 * @deprecated Use direct ViewListener registration instead
 */
final class LazyViewListener implements EventSubscriberInterface
{
    private ?ViewListener $instance = null;

    public function __construct(
        private readonly ContainerInterface $di,
        private readonly string $key = 'viewListener',
    ) {}

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return ViewListener::getSubscribedEvents();
    }

    /**
     * @param ControllerEvent $event
     *
     * @see ViewListener::onKernelController()
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $this->instance()->onKernelController($event);
    }

    /**
     * @param ViewEvent $event
     *
     * @see ViewListener::onKernelView()
     */
    public function onKernelView(ViewEvent $event): void
    {
        $this->instance()->onKernelView($event);
    }

    /**
     * @param ResponseEvent $event
     *
     * @see ViewListener::onKernelResponse()
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $this->instance()->onKernelResponse($event);
    }

    /**
     * Hide Di on debug
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'instance' => $this->instance
        ];
    }

    private function instance(): ViewListener
    {
        return $this->instance ??= $this->di->get($this->key);
    }
}

<?php

namespace Bdf\Templating\Listeners;

use Bdf\Templating\ConfigurableViewInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;

/**
 * Listener for automatically configure view format depending on the request
 */
class ViewListener implements EventSubscriberInterface
{
    /**
     * Définit toutes les ressources de la classe en fonction du format pour la plupart
     *
     * @var array
     */
    protected array $resources = [
        'layouts'  => [],
        
        'suffixes' => [
            'html' => '.html.php',
            'pjax' => '.html.php',
            'part' => '.part.php',
            'json' => '.json.php',
            'txt'  => '.txt.php',
            'pdf'  => '.pdf.php',
            'xls'  => '.xls.php',
            'xml'  => '.xml.php',
        ],

        'headers' => [
            'part' => [
                'Content-Type' => 'text/html',
            ],
            'json' => [
                'Content-Type' => 'application/json',
            ],
            'xls'  => [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Content-Disposition' => 'attachment; filename="export.xls"',
                'Content-Type'        => 'application/x-excel',
                'Expires'             => '0',
                'Pragma'              => 'no-cache',
            ],
        ]
    ];

    public function __construct(
        protected readonly ConfigurableViewInterface $view,
        /**
         * Default charset for response
         */
        protected readonly string $charset = 'UTF-8',
    ) {}

    /**
     * Set headers by type
     * array of ['json' => '.json.php']
     * 
     * @param array $suffixes
     */
    public function setSuffixes(array $suffixes): void
    {
        $this->resources['suffixes'] = array_merge($this->resources['suffixes'], $suffixes);
    }

    /**
     * Set headers by type
     * array of ['json' => 'base.json.php']
     * 
     * @param array $layouts
     */
    public function setLayouts(array $layouts): void
    {
        $this->resources['layouts'] = array_merge($this->resources['layouts'], $layouts);
    }

    /**
     * Set headers by type
     * array of ['jons' => ['header1' => 'value1', 'header2' => 'value2']]
     * 
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->resources['headers'] = array_merge_recursive($this->resources['headers'], $headers);
    }
    
    /**
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $format = $this->format($event->getRequest());
        
        if (isset($this->resources['suffixes'][$format])) {
            $this->view->setViewSuffix($this->resources['suffixes'][$format]);
        }

        if (isset($this->resources['layouts'][$format])) {
            $this->view->setDefaultLayout($this->resources['layouts'][$format]);
        }
    }

    /**
     * @param ViewEvent $event
     */
    public function onKernelView(ViewEvent $event): void
    {
        $response = $event->getControllerResult();

        if (!(
            null === $response
            || is_array($response)
            || $response instanceof Response
            || (is_object($response) && !method_exists($response, '__toString'))
        )) {
            $event->setResponse(new Response((string) $response));
        }
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request  = $event->getRequest();

        foreach ($this->getFormatHeaders($this->format($request)) as $header => $value) {
            $response->headers->set($header, $value);
        }

        $response->setCharset($this->charset);
        $response->prepare($request);
    }

    /**
     * @param string $type
     *
     * @return array
     */
    protected function getFormatHeaders(string $type): array
    {
        if (empty($this->resources['headers'][$type])) {
            return [];
        }

        return $this->resources['headers'][$type];
    }

    private function format(Request $request): string
    {
        $format = $request->getRequestFormat(null);

        if ($format === null) {
            foreach ($request->getAcceptableContentTypes() as $mimeType) {
                if ($format = $request->getFormat($mimeType)) {
                    break;
                }
            }

            if (!$format) {
                $format = 'html';
            }
        }

        return $format;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController'],
            KernelEvents::VIEW       => ['onKernelView'],
            KernelEvents::RESPONSE   => ['onKernelResponse']
        ];
    }
}

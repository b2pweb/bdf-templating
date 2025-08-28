<?php

namespace Bdf\Templating;

use Bdf\Templating\Filters\FilterInterface;
use Bdf\Templating\Helpers\B2pJs;
use Bdf\Templating\Helpers\Css;
use Bdf\Templating\Helpers\HelperInterface;
use Bdf\Templating\Helpers\Js;
use Bdf\Templating\Helpers\Parts;
use Bdf\Templating\TemplateResolver\TemplateResolverInterface;
use Closure;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

use function is_array;
use function iterator_to_array;
use function method_exists;

class PhpEngine implements EngineInterface, ConfigurableViewInterface
{
    use Extensions\Asset;
    use Extensions\Encoder;
    use Extensions\Escaper;

    /**
     * Layout name key. Use it in extend to use default layout attribute
     */
    const LAYOUT = ':base';

    /**
     * The default layout used if extend by self::LAYOUT
     */
    protected string $__defaultLayout = 'base';

    /**
     * The default view suffix
     *
     * @var string
     * @fixme cannot use type hint because bdf mail override this property
     */
    protected /*string*/ $__viewSuffix = '.html.php';

    /**
     * The current vars set in render
     *
     * @var array
     */
    protected array $__vars = [];

    /**
     * @var array<string, class-string<HelperInterface>>
     */
    protected array $__knownHelpers = [
        'parts' => Parts::class,
        'css'   => Css::class,
        'js'    => Js::class,
        'b2pJs' => B2pJs::class,
    ];

    /**
     * @var list<string>
     */
    protected array $__helperNamespaces = [];

    /**
     * @var array<string, HelperInterface>
     */
    protected array $__helpers = [];

    /**
     * Array of FilterInterface
     *
     * @var FilterInterface[]
     * @deprecated
     */
    protected array $__filters = [];

    /**
     * The current template hash in render
     *
     * @var string
     */
    protected string $__current = '';

    /**
     * List of parent templates of the current view
     *
     * The key correspond to the template hash {@see self::$__current},
     * and the value is the template name.
     *
     * @var array<string, string|null>
     */
    protected array $__parents = [];

    /**
     * @var list<object>
     */
    private array $__extensions = [];

    /**
     * Cache the extension method name to its object
     *
     * If the value is false, the method does not exist in any extension,
     * so the search will be skipped next time.
     *
     * @var array<string, object|false>
     */
    private array $__extensionMethods = [];

    public function __construct(
        protected readonly ContainerInterface $di,
        protected readonly TemplateResolverInterface $__templateResolver,
        /** @var Closure():FragmentHandler */
        protected readonly ?Closure $__fragmentHandlerResolver = null,

        /**
         * @var iterable<object>
         */
        iterable $extensions = [],
    ) {
        $this->__extensions = is_array($extensions) ? $extensions : iterator_to_array($extensions, false);
    }

    /**
     * Get the default layout name
     *
     * @return string
     */
    public function getDefaultLayout(): string
    {
        return $this->__defaultLayout;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultLayout($defaultLayout): void
    {
        $this->__defaultLayout = $defaultLayout;
    }

    /**
     * Get the view suffix
     *
     * @return string
     */
    public function getViewSuffix(): string
    {
        return $this->__viewSuffix;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewSuffix($viewSuffix): void
    {
        $this->__viewSuffix = (string) $viewSuffix;
    }

    /**
     * Call a helper or extension method
     *
     * @param string $name
     * @param array  $args
     *
     * @return HelperInterface|mixed
     */
    public function __call(string $name, array $args = []): mixed
    {
        $ext = $this->__extensionMethods[$name] ?? null;

        if ($ext) {
            return $ext->$name(...$args);
        }

        if ($ext === null) {
            foreach ($this->__extensions as $extension) {
                if (method_exists($extension, $name)) {
                    $this->__extensionMethods[$name] = $extension;

                    return $extension->$name(...$args);
                }
            }
        }

        $this->__extensionMethods[$name] = false;
        $helper = $this->getHelper($name);

        if (method_exists($helper, 'prepare')) {
            $helper->prepare(...$args);
        }

        return $helper;
    }

    /**
     * Get helper
     *
     * @param string|class-string<H> $name
     *
     * @return HelperInterface
     * @psalm-return ($name is class-string<H> ? H : HelperInterface)
     * @template H as HelperInterface
     */
    public function getHelper(string $name): HelperInterface
    {
        if (!isset($this->__helpers[$name])) {
            $this->loadHelper($name);
        }

        return $this->__helpers[$name];
    }

    /**
     * Add a helper
     *
     * @param HelperInterface $helper
     */
    public function addHelper(HelperInterface $helper): void
    {
        if (method_exists($helper, 'setDI')) {
            $helper->setDI($this->di);
        }

        if (method_exists($helper, 'setView')) {
            $helper->setView($this);
        }

        if (method_exists($helper, 'initialize')) {
            $helper->initialize();
        }

        $this->__helpers[$helper->getName()] = $helper;
        $this->__helpers[get_class($helper)] = $helper;
    }

    /**
     * Dynamic load of helper
     *
     * @param string $name
     */
    protected function loadHelper(string $name): void
    {
        if (class_exists($name) && is_subclass_of($name, HelperInterface::class)) {
            $className = $name;
        } elseif (isset($this->__knownHelpers[$name])) {
            $className = $this->__knownHelpers[$name];
        } else {
            $name = ucfirst($name);
            $found = false;

            foreach ($this->__helperNamespaces as $namespace) {
                $className = $namespace.$name;

                if (class_exists($className)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new \RuntimeException('Helper "'.$name.'" not found.');
            }
        }

        $helper = $this->di->has($className) ? $this->di->get($className) : new $className;

        $this->addHelper($helper);
    }

    /**
     * Set helper namespaces for dynamic load
     *
     * @param list<string> $namespaces
     *
     * @return self
     */
    public function setHelperNamespaces(array $namespaces): self
    {
        $this->__helperNamespaces = [];

        foreach ($namespaces as $namespace) {
            $this->addHelperNamespace($namespace);
        }

        return $this;
    }

    /**
     * Add helper namespace
     *
     * @param string $namespace
     */
    public function addHelperNamespace(string $namespace): void
    {
        if (empty($namespace)) {
            $this->__helperNamespaces[] = '';
        } else {
            $this->__helperNamespaces[] = rtrim($namespace, '\\') . '\\';
        }
    }

    /**
     * Prepend helper namespace
     *
     * @param string $namespace
     */
    public function prependHelperNamespace(string $namespace): void
    {
        if (empty($namespace)) {
            array_unshift($this->__helperNamespaces, '');
        } else {
            array_unshift($this->__helperNamespaces, rtrim($namespace, '\\') . '\\');
        }
    }

    /**
     * Get all helper namespaces
     *
     * @return array
     */
    public function getHelperNamespaces(): array
    {
        return $this->__helperNamespaces;
    }

    /**
     * Declare a helper class name
     *
     * @param string $name
     * @param class-string<HelperInterface> $className
     */
    public function declareHelper(string $name, string $className): void
    {
        $this->__knownHelpers[$name] = $className;
    }

    /**
     * Add a new extension object, which will provide new methods to the view
     *
     * @param object $extension
     * @return void
     */
    public function addExtension(object $extension): void
    {
        $this->__extensions[] = $extension;
    }

    /**
     * @param string $key
     *
     * @return mixed Retourne avec reference
     */
    public function __get(string $key): mixed
    {
        return $this->__vars[$key];
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->__vars[$key]);
    }

    /**
     * @return array
     */
    public function vars(): array
    {
        return $this->__vars;
    }

    /**
     * {@inheritdoc}
     */
    public function render($name, array $parameters = []): string
    {
        // save previous info for recursion
        $__previousVars = $this->__vars;

        $__FILE__ = $this->resolveTemplate($name);

        $this->__current = $__KEY__ = hash('sha256', $__FILE__);
        $this->__parents[$__KEY__] = null;
        $this->__vars = $parameters;

        try {
            ob_start();

            (function () use ($__FILE__) {
                require $__FILE__;
            })();

            $content = $this->filter(ob_get_clean());
        } catch (\Exception $e) {
            ob_end_clean();

            $this->__vars = $__previousVars;

            throw $e;
        }

        if ($this->__parents[$__KEY__]) {
            $parts = $this->getHelper('parts');

            $__currentContent = $parts->get('_content', null);

            $parts->set('_content', $content);
            $content = $this->render($this->__parents[$__KEY__], $parameters);
            $parts->set('_content', $__currentContent);
        }

        $this->__vars = $__previousVars;

        return $content;
    }

    /**
     * Include a partial template in current context
     *
     * @param string $__name
     *
     * @return string
     */
    public function partial(string $__name): string
    {
        try {
            ob_start();

            require $this->resolveTemplate($__name);

            $content = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();

            throw $e;
        }

        return $content;
    }

    /**
     * Resolve the template name to file name
     *
     * @param string $template       The template name. Could have extension. If not the current or default suffix will be used
     *
     * @return string                The path name
     */
    public function resolveTemplate(string $template): string
    {
        if (str_contains($template, '.') === false) {
            $template .= $this->__viewSuffix;
        }

        return $this->__templateResolver->resolve($template);
    }

    /**
     * Set the render filters
     *
     * @param array $filters
     * @deprecated filter system is deprecated, do not use it anymore
     */
    public function setFilters(array $filters)
    {
        $this->__filters = [];

        $this->addFilters($filters);
    }

    /**
     * Add render filters
     *
     * @param array|FilterInterface $filters
     * @deprecated filter system is deprecated, do not use it anymore
     */
    public function addFilters($filters)
    {
        if (!is_array($filters)) {
            $filters = array($filters);
        }

        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * Add a render filter
     *
     * @param FilterInterface $filter
     * @deprecated filter system is deprecated, do not use it anymore
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->__filters[] = $filter;
    }

    /**
     * Filter the render content
     *
     * @param string $content
     *
     * @return string
     * @deprecated filter system is deprecated, do not use it anymore
     */
    public function filter($content)
    {
        foreach ($this->__filters as $filter) {
            $content = $filter->filter($content);
        }

        return $content;
    }

    /**
     * @param string $template
     */
    public function extend(string $template): void
    {
        if ($template === self::LAYOUT) {
            $template = $this->__defaultLayout;
        }

        $this->__parents[$this->__current] = $template;
    }

    /**
     * @param string $controller
     * @param array  $attributes
     * @param array  $query
     *
     * @return ControllerReference
     */
    public function controller(string $controller, array $attributes = [], array $query = []): ControllerReference
    {
        return new ControllerReference($controller, $attributes, $query);
    }

    /**
     * @param string|ControllerReference $uri
     * @param array                      $options
     *
     * @return string
     */
    public function fragment(string|ControllerReference $uri, array $options = []): string
    {
        $strategy = $options['strategy'] ?? 'inline';
        unset($options['strategy']);

        $fragmentHandler = $this->__fragmentHandlerResolver ? ($this->__fragmentHandlerResolver)() : $this->di->get('fragmentHandler');

        return $fragmentHandler->render($uri, $strategy, $options);
    }
}

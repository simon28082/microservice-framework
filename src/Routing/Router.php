<?php

namespace CrCms\Microservice\Routing;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Events\Dispatcher;
use CrCms\Microservice\Server\Contracts\RequestContract;

/**
 * Class Router.
 */
class Router
{
    use Macroable {
        __call as macroCall;
    }

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The route collection instance.
     *
     * @var \CrCms\Microservice\Routing\RouteCollection
     */
    protected $routes;

    /**
     * The currently dispatched route instance.
     *
     * @var \CrCms\Microservice\Routing\Route
     */
    protected $current;

    /**
     * @var RequestContract
     */
    protected $currentRequest;

    /**
     * All of the short-hand keys for middlewares.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * All of the middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    public $middlewarePriority = [];

    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * Create a new Router instance.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @param \Illuminate\Container\Container         $container
     *
     * @return void
     */
    public function __construct(Dispatcher $events, Container $container = null)
    {
        $this->events = $events;
        $this->routes = new RouteCollection();
        $this->container = $container ?: new Container();
    }

    /**
     * @param $name
     * @param $action
     *
     * @return \CrCms\Microservice\Routing\Route
     */
    public function register($name, $action, array $options = [])
    {
        if (
            is_array($action) ||
            (is_string($action) && strpos($action, '@') === false)
        ) {
            return $this->multiple($name, $action, $options);
        } else {
            return $this->single($name, $action);
        }
    }

    /**
     * @param $name
     * @param $action
     *
     * @return \CrCms\Microservice\Routing\Route
     */
    public function single($name, $action)
    {
        return $this->addRoute($name, $action);
    }

    /**
     * @param $name
     * @param $action
     *
     * @throws \ReflectionException
     *
     * @return \CrCms\Microservice\Routing\Route
     */
    public function multiple($name, $action, array $options = [])
    {
        $namespaceAction = $this->convertToControllerAction($action);
        $methods = (new ReflectionAction($this))->getMethods($namespaceAction['uses']);
        if (isset($options['only'])) {
            $methods = array_intersect($methods, $options['only']);
        } elseif (isset($options['except'])) {
            $methods = array_diff($methods, $options['except']);
        }
        foreach ($methods as $method) {
            $uses = isset($action['uses']) ? "{$action['uses']}@{$method}" : "{$action}@{$method}";
            $this->single("{$name}.{$method}", array_merge($namespaceAction, ['controller' => $uses, 'uses' => $uses]));
        }
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param array           $attributes
     * @param \Closure|string $routes
     *
     * @return void
     */
    public function group(array $attributes, $routes)
    {
        $this->updateGroupStack($attributes);

        // Once we have updated the group stack, we'll load the provided routes and
        // merge in the group's attributes when the routes are created. After we
        // have created the routes, we will pop the attributes off the stack.
        $this->loadRoutes($routes);

        array_pop($this->groupStack);
    }

    /**
     * Update the group stack with the given attributes.
     *
     * @param array $attributes
     *
     * @return void
     */
    protected function updateGroupStack(array $attributes)
    {
        if (! empty($this->groupStack)) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given array with the last group stack.
     *
     * @param array $new
     *
     * @return array
     */
    public function mergeWithLastGroup($new)
    {
        $old = end($this->groupStack);

        if (isset($new['namespace'])) {
            $namespace = isset($old['namespace']) && strpos($new['namespace'], '\\') !== 0
                ? trim($old['namespace'], '\\').'\\'.trim($new['namespace'], '\\')
                : trim($new['namespace'], '\\');
        } else {
            $namespace = $old['namespace'] ?? null;
        }

        $new = array_merge($new, [
            'namespace' => $namespace,
        ]);

        return array_merge_recursive(Arr::except(
            $old, ['namespace']
        ), $new);
    }

    /**
     * Load the provided routes.
     *
     * @param \Closure|string $routes
     *
     * @return void
     */
    protected function loadRoutes($routes)
    {
        if ($routes instanceof Closure) {
            $routes($this);
        } else {
            $router = $this;

            require $routes;
        }
    }

    /**
     * @param $name
     * @param $action
     *
     * @return \CrCms\Microservice\Routing\Route
     */
    public function addRoute($name, $action)
    {
        return $this->routes->add($this->createRoute($name, $action));
    }

    /**
     * @param $name
     * @param $action
     *
     * @return \CrCms\Microservice\Routing\Route
     */
    protected function createRoute($name, $action)
    {
        // If the route is routing to a controller we will parse the route action into
        // an acceptable array format before registering it and creating this route
        // instance itself. We need to build the Closure that will call this out.
        if ($this->actionReferencesController($action)) {
            $action = $this->convertToControllerAction($action);
        }

        $route = $this->newRoute(
            $name, $action
        );

        // If we have groups that need to be merged, we will merge them now after this
        // route has already been created and is ready to go. After we're done with
        // the merge we will be ready to return the route back out to the caller.
        if ($this->hasGroupStack()) {
            $this->mergeGroupAttributesIntoRoute($route);
        }

        return $route;
    }

    /**
     * Determine if the action is routing to a controller.
     *
     * @param array $action
     *
     * @return bool
     */
    protected function actionReferencesController($action)
    {
        if (! $action instanceof Closure) {
            return is_string($action) || (isset($action['uses']) && is_string($action['uses']));
        }

        return false;
    }

    /**
     * Add a controller based route action to the action array.
     *
     * @param array|string $action
     *
     * @return array
     */
    protected function convertToControllerAction($action)
    {
        if (is_string($action)) {
            $action = ['uses' => $action];
        }

        // Here we'll merge any group "uses" statement if necessary so that the action
        // has the proper clause for this property. Then we can simply set the name
        // of the controller on the action and return the action array for usage.
        if (! empty($this->groupStack)) {
            $action['uses'] = $this->prependGroupNamespace($action['uses']);
        }

        // Here we will set this controller name on the action array just so we always
        // have a copy of it for reference if we need it. This can be used while we
        // search for a controller name or do some other type of fetch operation.
        $action['controller'] = $action['uses'];

        return $action;
    }

    /**
     * Prepend the last group namespace onto the use clause.
     *
     * @param string $class
     *
     * @return string
     */
    protected function prependGroupNamespace($class)
    {
        $group = end($this->groupStack);

        return isset($group['namespace']) && strpos($class, '\\') !== 0
            ? $group['namespace'].'\\'.$class : $class;
    }

    /**
     * @param $name
     * @param $action
     *
     * @return \CrCms\Microservice\Routing\Route
     */
    protected function newRoute($name, $action)
    {
        return (new Route($name, $action))
            ->setRouter($this)
            ->setContainer($this->container);
    }

    /**
     * @param \CrCms\Microservice\Routing\Route $route
     */
    protected function mergeGroupAttributesIntoRoute(Route $route)
    {
        $route->setAction($this->mergeWithLastGroup($route->getAction()));
    }

    /**
     * @param RequestContract $request
     *
     * @return mixed
     */
    public function dispatch(RequestContract $request)
    {
        $this->currentRequest = $request;

        return $this->dispatchToRoute($request);
    }

    /**
     * @param RequestContract $request
     *
     * @return mixed
     */
    public function dispatchToRoute(RequestContract $request)
    {
        return $this->runRoute($request, $this->findRoute($request));
    }

    /**
     * @param RequestContract $request
     *
     * @return array
     */
    protected function findRoute(RequestContract $request)
    {
        $this->current = $route = $this->routes->match($request);

        $this->container->instance(Route::class, $route);

        return $route;
    }

    /**
     * @param RequestContract $request
     * @param Route           $route
     *
     * @return mixed
     */
    protected function runRoute(RequestContract $request, Route $route)
    {
        $request->setRoute($route);

        $this->events->dispatch(new Events\RouteMatched($route, $request));

        return $this->runRouteWithinStack($route, $request);
//        return $this->prepareResponse($service,
//            $this->runRouteWithinStack($route, $service)
//        );
    }

    /**
     * @param Route           $route
     * @param RequestContract $request
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return mixed
     */
    protected function runRouteWithinStack(Route $route, RequestContract $request)
    {
        $shouldSkipMiddleware = $this->container->bound('middleware.disable') &&
            $this->container->make('middleware.disable') === true;

        $middleware = $shouldSkipMiddleware ? [] : $this->gatherRouteMiddleware($route);

        return (new Pipeline($this->container))
            ->send($request)
            ->through($middleware)
            ->then(function ($request) use ($route) {
                return $this->prepareResponse(
                    $request, $route->run()
                );
            });
    }

    /**
     * @param \CrCms\Microservice\Routing\Route $route
     *
     * @return array
     */
    public function gatherRouteMiddleware(Route $route)
    {
        $middleware = collect($route->gatherMiddleware())->map(function ($name) {
            return (array) MiddlewareNameResolver::resolve($name, $this->middleware, $this->middlewareGroups);
        })->flatten();

        return $this->sortMiddleware($middleware);
    }

    /**
     * @param Collection $middlewares
     *
     * @return array
     */
    protected function sortMiddleware(Collection $middlewares)
    {
        return (new SortedMiddleware($this->middlewarePriority, $middlewares))->all();
    }

    /**
     * @param RequestContract $request
     * @param $response
     */
    public function prepareResponse(RequestContract $request, $response)
    {
        // @todo 这里先这样，后面要做一个bind()方便注入修改
        $class = get_class($request);
        $responseClass = str_replace(strrchr(get_class($request), '\\'), '\\Response', $class);

        return $responseClass::createResponse($response);
    }

    /**
     * Register a route matched event listener.
     *
     * @param string|callable $callback
     *
     * @return void
     */
    public function matched($callback)
    {
        $this->events->listen(Events\RouteMatched::class, $callback);
    }

    /**
     * Get all of the defined middleware short-hand names.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Register a short-hand name for a middleware.
     *
     * @param string $name
     * @param string $class
     *
     * @return $this
     */
    public function aliasMiddleware($name, $class)
    {
        $this->middleware[$name] = $class;

        return $this;
    }

    /**
     * Check if a middlewareGroup with the given name exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasMiddlewareGroup($name)
    {
        return array_key_exists($name, $this->middlewareGroups);
    }

    /**
     * Get all of the defined middleware groups.
     *
     * @return array
     */
    public function getMiddlewareGroups()
    {
        return $this->middlewareGroups;
    }

    /**
     * Register a group of middleware.
     *
     * @param string $name
     * @param array  $middleware
     *
     * @return $this
     */
    public function middlewareGroup($name, array $middleware)
    {
        $this->middlewareGroups[$name] = $middleware;

        return $this;
    }

    /**
     * Add a middleware to the beginning of a middleware group.
     *
     * If the middleware is already in the group, it will not be added again.
     *
     * @param string $group
     * @param string $middleware
     *
     * @return $this
     */
    public function prependMiddlewareToGroup($group, $middleware)
    {
        if (isset($this->middlewareGroups[$group]) && ! in_array($middleware, $this->middlewareGroups[$group])) {
            array_unshift($this->middlewareGroups[$group], $middleware);
        }

        return $this;
    }

    /**
     * Add a middleware to the end of a middleware group.
     *
     * If the middleware is already in the group, it will not be added again.
     *
     * @param string $group
     * @param string $middleware
     *
     * @return $this
     */
    public function pushMiddlewareToGroup($group, $middleware)
    {
        if (! array_key_exists($group, $this->middlewareGroups)) {
            $this->middlewareGroups[$group] = [];
        }

        if (! in_array($middleware, $this->middlewareGroups[$group])) {
            $this->middlewareGroups[$group][] = $middleware;
        }

        return $this;
    }

    /**
     * Determine if the router currently has a group stack.
     *
     * @return bool
     */
    public function hasGroupStack()
    {
        return ! empty($this->groupStack);
    }

    /**
     * Get the current group stack for the router.
     *
     * @return array
     */
    public function getGroupStack()
    {
        return $this->groupStack;
    }

    /**
     * @return RequestContract
     */
    public function getCurrentRequest()
    {
        return $this->currentRequest;
    }

    /**
     * @return \CrCms\Microservice\Routing\Route
     */
    public function getCurrentRoute()
    {
        return $this->current();
    }

    /**
     * @return \CrCms\Microservice\Routing\Route
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * Check if a route with the given name exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        $names = is_array($name) ? $name : func_get_args();

        foreach ($names as $value) {
            if (! $this->routes->hasNamedRoute($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the current route name.
     *
     * @return string|null
     */
    public function currentRouteName()
    {
        return $this->current() ? $this->current()->getName() : null;
    }

    /**
     * Alias for the "currentRouteNamed" method.
     *
     * @param mixed ...$patterns
     *
     * @return bool
     */
    public function is(...$patterns)
    {
        return $this->currentRouteNamed(...$patterns);
    }

    /**
     * Determine if the current route matches a pattern.
     *
     * @param mixed ...$patterns
     *
     * @return bool
     */
    public function currentRouteNamed(...$patterns)
    {
        return $this->current() && $this->current()->named(...$patterns);
    }

    /**
     * Get the current route action.
     *
     * @return string|null
     */
    public function currentRouteAction()
    {
        if ($this->current()) {
            return $this->current()->getAction()['controller'] ?? null;
        }
    }

    /**
     * Alias for the "currentRouteUses" method.
     *
     * @param array ...$patterns
     *
     * @return bool
     */
    public function uses(...$patterns)
    {
        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $this->currentRouteAction())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the current route action matches a given action.
     *
     * @param string $action
     *
     * @return bool
     */
    public function currentRouteUses($action)
    {
        return $this->currentRouteAction() == $action;
    }

    /**
     * Get the underlying route collection.
     *
     * @return \CrCms\Microservice\Routing\RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Set the route collection instance.
     *
     * @param \CrCms\Microservice\Routing\RouteCollection $routes
     *
     * @return void
     */
    public function setRoutes(RouteCollection $routes)
    {
        foreach ($routes as $route) {
            $route->setRouter($this)->setContainer($this->container);
        }

        $this->routes = $routes;

        $this->container->instance('routes', $this->routes);
    }

    /**
     * Dynamically handle calls into the router instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        if ($method === 'middleware') {
            return (new RouteRegistrar($this))->attribute($method, is_array($parameters[0]) ? $parameters[0] : $parameters);
        }

        return (new RouteRegistrar($this))->attribute($method, $parameters[0]);
    }
}

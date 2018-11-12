<?php

namespace CrCms\Microservice\Server;

use CrCms\Microservice\Foundation\Application;
use CrCms\Microservice\Server\Contracts\ResponseContract;
use CrCms\Microservice\Server\Contracts\ServiceContract;
use Exception;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;
use CrCms\Microservice\Routing\Pipeline;
use Illuminate\Support\Facades\Facade;
use CrCms\Microservice\Server\Contracts\ExceptionHandlerContract;
use CrCms\Microservice\Server\Contracts\KernelContract;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use CrCms\Microservice\Routing\Router;

class Kernel implements KernelContract
{
    /**
     * @var ApplicationContract|Application
     */
    protected $app;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var array
     */
    protected $bootstrappers = [
        \CrCms\Microservice\Bootstrap\LoadEnvironmentVariables::class,
        \CrCms\Microservice\Bootstrap\LoadConfiguration::class,
        \CrCms\Microservice\Bootstrap\HandleExceptions::class,
        \CrCms\Microservice\Bootstrap\RegisterFacades::class,
        \CrCms\Microservice\Bootstrap\RegisterProviders::class,
        \CrCms\Microservice\Bootstrap\BootProviders::class,
    ];

    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        //\CrCms\Foundation\MicroService\Middleware\HashMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
    ];

    /**
     * Create a new HTTP kernel instance.
     *
     * @param  ApplicationContract  $app
     * @param  Router  $router
     * @return void
     */
    public function __construct(ApplicationContract $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;

        $router->middlewarePriority = $this->middlewarePriority;

        foreach ($this->middlewareGroups as $key => $middleware) {
            $router->middlewareGroup($key, $middleware);
        }

        foreach ($this->routeMiddleware as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }
    }

    public function handle(ServiceContract $service): ResponseContract
    {
        try {
            $response = $this->sendRequestThroughRouter($service);
        } catch (Exception $e) {
            $this->reportException($e);
            $response = $this->renderException($service, $e);
        } catch (Throwable $e) {
            $this->reportException($e = new FatalThrowableError($e));
            $response = $this->renderException($e);
        }

//        $this->app['events']->dispatch(
//            new RequestHandled($request, $response)
//        );

        return $response;
    }

    protected function sendRequestThroughRouter(ServiceContract $service)
    {
        $this->app->instance('service', $service);

        Facade::clearResolvedInstance('service');

        $this->bootstrap();

        return (new Pipeline($this->app))
            ->send($service)
            ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
            ->then($this->dispatchToRouter());
    }

    /**
     * Bootstrap the application for HTTP requests.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers());
        }
    }

    /**
     * Get the route dispatcher callback.
     *
     * @return \Closure
     */
    protected function dispatchToRouter()
    {
        return function ($service) {
            $this->app->instance('service', $service);

            return $this->router->dispatch($service);
        };
    }

    /**
     * @param ServiceContract $service
     * @return mixed|void
     */
    public function terminate(ServiceContract $service)
    {
        $this->terminateMiddleware($service);

        $this->app->terminate();
    }

    protected function terminateMiddleware(ServiceContract $service)
    {
        $middlewares = $this->app->shouldSkipMiddleware() ? [] : array_merge(
            $this->gatherRouteMiddleware($service),
            $this->middleware
        );

        foreach ($middlewares as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            list($name) = $this->parseMiddleware($middleware);

            $instance = $this->app->make($name);

            if (method_exists($instance, 'terminate')) {
                $instance->terminate($service);
            }
        }
    }

    protected function gatherRouteMiddleware(ServiceContract $service)
    {
        if ($route = $service->route()) {
            return $this->router->gatherRouteMiddleware($route);
        }

        return [];
    }

    protected function parseMiddleware($middleware)
    {
        list($name, $parameters) = array_pad(explode(':', $middleware, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Determine if the kernel has a given middleware.
     *
     * @param  string  $middleware
     * @return bool
     */
    public function hasMiddleware($middleware)
    {
        return in_array($middleware, $this->middleware);
    }

    /**
     * Add a new middleware to beginning of the stack if it does not already exist.
     *
     * @param  string  $middleware
     * @return $this
     */
    public function prependMiddleware($middleware)
    {
        if (array_search($middleware, $this->middleware) === false) {
            array_unshift($this->middleware, $middleware);
        }

        return $this;
    }

    /**
     * Add a new middleware to end of the stack if it does not already exist.
     *
     * @param  string  $middleware
     * @return $this
     */
    public function pushMiddleware($middleware)
    {
        if (array_search($middleware, $this->middleware) === false) {
            $this->middleware[] = $middleware;
        }

        return $this;
    }

    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }

    protected function reportException(Exception $e)
    {
        $this->app[ExceptionHandlerContract::class]->report($e);
    }

    protected function renderException(ServiceContract $service, Exception $e)
    {
        return $this->app[ExceptionHandlerContract::class]->render($e);
    }

    public function getApplication(): ApplicationContract
    {
        return $this->app;
    }
}
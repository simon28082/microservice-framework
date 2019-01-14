<?php

namespace CrCms\Microservice\Server;

use Exception;
use Throwable;
use CrCms\Microservice\Routing\Router;
use Illuminate\Support\Facades\Facade;
use CrCms\Microservice\Routing\Pipeline;
use CrCms\Microservice\Foundation\Application;
use CrCms\Microservice\Server\Events\RequestHandled;
use CrCms\Microservice\Server\Events\RequestHandling;
use CrCms\Microservice\Server\Contracts\KernelContract;
use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Contracts\ResponseContract;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

/**
 * Class Kernel.
 */
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
        \CrCms\Microservice\Server\Middleware\CheckForMaintenanceModeMiddleware::class,
        \CrCms\Microservice\Server\Middleware\DataEncryptDecryptMiddleware::class,
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
     * 优先加载的中间件，必须是$middleware和$middlewareGroups中定义.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \CrCms\Microservice\Server\Middleware\CheckForMaintenanceModeMiddleware::class,
        \CrCms\Microservice\Server\Middleware\DataEncryptDecryptMiddleware::class,
    ];

    /**
     * Create a new HTTP kernel instance.
     *
     * @param ApplicationContract $app
     * @param Router              $router
     *
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

    /**
     * @param RequestContract $request
     *
     * @throws \ReflectionException
     *
     * @return ResponseContract
     */
    public function handle(RequestContract $request): ResponseContract
    {
        try {
            $response = $this->sendRequestThroughRouter($request);
        } catch (Exception $e) {
            $this->reportException($e);
            $response = $this->renderException($request, $e);
        } catch (Throwable $e) {
            $this->reportException($e = new FatalThrowableError($e));
            $response = $this->renderException($request, $e);
        }

        $this->app['events']->dispatch(
            new RequestHandled($request, $response)
        );

        return $response;
    }

    /**
     * @param RequestContract $request
     *
     * @return mixed
     */
    protected function sendRequestThroughRouter(RequestContract $request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $this->bootstrap();

        if ((bool) $response = $this->app['events']->until(
            new RequestHandling($request)
        )) {
            return $response;
        }

        return (new Pipeline($this->app))
            ->send($request)
            ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
            ->then($this->dispatchToRouter());
    }

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
        return function (RequestContract $request) {
            $this->app->instance('request', $request);

            return $this->router->dispatch($request);
        };
    }

    /**
     * @param RequestContract  $request
     * @param ResponseContract $response
     *
     * @return mixed|void
     */
    public function terminate(RequestContract $request, ResponseContract $response)
    {
        $this->terminateMiddleware($request, $response);

        $this->app->terminate();
    }

    /**
     * @param RequestContract  $request
     * @param ResponseContract $response
     */
    protected function terminateMiddleware(RequestContract $request, ResponseContract $response)
    {
        $middlewares = $this->app->shouldSkipMiddleware() ? [] : array_merge(
            $this->gatherRouteMiddleware($request),
            $this->middleware
        );

        foreach ($middlewares as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }

            list($name) = $this->parseMiddleware($middleware);

            $instance = $this->app->make($name);

            if (method_exists($instance, 'terminate')) {
                $instance->terminate($request, $response);
            }
        }
    }

    /**
     * @param RequestContract $request
     *
     * @return array
     */
    protected function gatherRouteMiddleware(RequestContract $request)
    {
        try {
            if ($route = $request->getRoute()) {
                return $this->router->gatherRouteMiddleware($route);
            }
        } catch (Throwable $e) {
            return [];
        }

        return [];
    }

    /**
     * @param $middleware
     *
     * @return array
     */
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
     * @param string $middleware
     *
     * @return bool
     */
    public function hasMiddleware($middleware)
    {
        return in_array($middleware, $this->middleware);
    }

    /**
     * Add a new middleware to beginning of the stack if it does not already exist.
     *
     * @param string $middleware
     *
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
     * @param string $middleware
     *
     * @return $this
     */
    public function pushMiddleware($middleware)
    {
        if (array_search($middleware, $this->middleware) === false) {
            $this->middleware[] = $middleware;
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }

    /**
     * @param Exception $e
     */
    protected function reportException(Exception $e)
    {
        $this->app[ExceptionHandlerContract::class]->report($e);
    }

    /**
     * @param RequestContract $request
     * @param Exception       $e
     *
     * @return mixed
     */
    protected function renderException(RequestContract $request, Exception $e)
    {
        return $this->app[ExceptionHandlerContract::class]->render($request, $e);
    }

    /**
     * @return ApplicationContract
     */
    public function getApplication(): ApplicationContract
    {
        return $this->app;
    }
}

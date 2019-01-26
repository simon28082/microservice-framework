<?php

namespace CrCms\Microservice\Foundation;

use CrCms\Foundation\Transporters\Contracts\DataProviderContract;
use CrCms\Foundation\Transporters\DataProvider;
use Exception;
use Throwable;
use CrCms\Microservice\Routing\Router;
use Illuminate\Support\Facades\Facade;
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
     * @var ApplicationContract
     */
    protected $app;

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
     * The application request middleware
     *
     * @var array
     */
    protected $requestMiddleware = [
        \CrCms\Microservice\Foundation\Middleware\CheckForMaintenanceModeMiddleware::class,
    ];

    /**
     * The application data transport middleware
     *
     * @var array
     */
    protected $transportMiddleware = [
        \CrCms\Microservice\Server\Middleware\DataEncryptDecryptMiddleware::class,
    ];

    /**
     * Create a new HTTP kernel instance.
     *
     * @param ApplicationContract $app
     * @param Router $router
     *
     * @return void
     */
    public function __construct(ApplicationContract $app)
    {
        $this->app = $app;
    }

    public function request(RequestContract $request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        try {
            $response = (new \Illuminate\Pipeline\Pipeline($this->app))
                ->send($request)
                ->through($this->requestMiddleware)
                ->then(function ($request) {
                    return $request;
                });
        } catch (Exception $e) {
            $this->reportException($e);
            $response = $this->renderException($request, $e);
        } catch (Throwable $e) {
            $this->reportException($e = new FatalThrowableError($e));
            $response = $this->renderException($request, $e);
        }

        return $response;
    }

    public function transport(string $data, ?RequestContract $request = null): ResponseContract
    {
        try {
            $data = $this->app->make('server.packer')->unpack($data);

            $this->app->instance('data.provider', new DataProvider(
                array_merge($data['data'],['_request' => $request])
            ));

            Facade::clearResolvedInstance('data.provider');

            $response = $this->app->make('caller.match')->match($data['call'], $this->app->get('data.provider'));

            dd($response);
        } catch (Exception $e) {
            $this->reportException($e);
            $response = $this->renderException(null, $e);
        } catch (Throwable $e) {
            $this->reportException($e = new FatalThrowableError($e));
            $response = $this->renderException(null, $e);
        }

        return $response;
    }

    /**
     * bootstrap
     *
     * @return void
     */
    public function bootstrap(): void
    {
        if (!$this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers());
        }
    }

    /**
     * @return ApplicationContract
     */
    public function getApplication(): ApplicationContract
    {
        return $this->app;
    }

    /**
     * @param RequestContract|DataProviderContract $data
     * @param ResponseContract $response
     *
     * @return mixed|void
     */
    public function terminate($data, ResponseContract $response)
    {
        $this->terminateMiddleware($data, $response);

        $this->app->terminate();
    }

    /**
     * @param RequestContract|DataProviderContract $request
     * @param ResponseContract $response
     */
    protected function terminateMiddleware($data, ResponseContract $response)
    {
        $middlewares = array_merge(
            $this->requestMiddleware,
            $this->transportMiddleware,
            $this->app->make('caller.match')->getCallerMiddleware()
        );

        foreach ($middlewares as $middleware) {
            if (!is_string($middleware)) {
                continue;
            }

            list($name) = $this->parseMiddleware($middleware);

            $instance = $this->app->make($name);

            if (method_exists($instance, 'terminate')) {
                $instance->terminate($data, $response);
            }
        }
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
     * @param Exception $e
     *
     * @return mixed
     */
    protected function renderException(?RequestContract $request, Exception $e)
    {
        return $this->app[ExceptionHandlerContract::class]->render($request, $e);
    }
}

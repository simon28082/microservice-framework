<?php

namespace CrCms\Microservice\Routing;

use Illuminate\Support\ServiceProvider;
//use Psr\Http\Message\ResponseInterface;
//use Psr\Http\Message\ServerRequestInterface;
use CrCms\Microservice\Routing\Contracts\ControllerDispatcher as ControllerDispatcherContract;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouter();
        $this->registerControllerDispatcher();
    }

    /**
     * Register the router instance.
     *
     * @return void
     */
    protected function registerRouter()
    {
        $this->app->singleton('router', function ($app) {
            return new Router($app['events'], $app);
        });
    }

    /**
     * Register the controller dispatcher.
     *
     * @return void
     */
    protected function registerControllerDispatcher()
    {
        $this->app->singleton(ControllerDispatcherContract::class, function ($app) {
            return new ControllerDispatcher($app);
        });
    }
}

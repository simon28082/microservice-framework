<?php

namespace CrCms\Microservice\Routing;

//use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Traits\ForwardsCalls;

class RouteServiceProvider extends ServiceProvider
{
use ForwardsCalls;
    public function boot()
    {
        require base_path('routes/service.php');
//        if (file_exists($routePath)) {
//            Route::middleware('micro_service')
//                ->group($routePath);
//        }
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapServiceRoutes();
    }

    /**
     * @return void
     */
    protected function mapServiceRoutes(): void
    {
        $routePath = base_path('routes/service.php');
        if (file_exists($routePath)) {
            Route::middleware('micro_service')
                ->group($routePath);
        }
    }

    public function __call($method, $parameters)
    {
        return $this->forwardCallTo(
            $this->app->make(Router::class), $method, $parameters
        );
    }
}

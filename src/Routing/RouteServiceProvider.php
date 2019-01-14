<?php

namespace CrCms\Microservice\Routing;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Traits\ForwardsCalls;

/**
 * Class RouteServiceProvider.
 */
class RouteServiceProvider extends ServiceProvider
{
    use ForwardsCalls;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $routePath = base_path('routes/service.php');
        if (file_exists($routePath)) {
            require $routePath;
        }
    }

    public function register(): void
    {
    }

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo(
            $this->app->make(Router::class), $method, $parameters
        );
    }
}

<?php

namespace CrCms\Microservice\Server\Middleware;

use CrCms\Microservice\Server\Contracts\ServiceContract;
use CrCms\Microservice\Server\Exceptions\ServiceUnavailableException;
use Illuminate\Contracts\Foundation\Application;
use Closure;

/**
 * Class CheckForMaintenanceModeMiddleware
 * @package CrCms\Microservice\Server\Middleware
 */
class CheckForMaintenanceModeMiddleware
{
    /**
     * CheckForMaintenanceMode constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param ServiceContract $service
     * @param Closure $next
     * @return mixed
     */
    public function handle(ServiceContract $service, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            //$data = json_decode(file_get_contents($this->app->storagePath() . '/framework/down'), true);
            throw new ServiceUnavailableException("The service is maintaining state", 503);
        }

        return $next($service);
    }
}
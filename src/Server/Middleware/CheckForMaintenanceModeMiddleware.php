<?php

namespace CrCms\Microservice\Server\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Exceptions\ServiceUnavailableException;

/**
 * Class CheckForMaintenanceModeMiddleware.
 */
class CheckForMaintenanceModeMiddleware
{
    /**
     * CheckForMaintenanceMode constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param RequestContract $request
     * @param Closure         $next
     *
     * @return mixed
     */
    public function handle(RequestContract $request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            //$data = json_decode(file_get_contents($this->app->storagePath() . '/framework/down'), true);
            throw new ServiceUnavailableException('The service is maintaining state', 503);
        }

        return $next($request);
    }
}

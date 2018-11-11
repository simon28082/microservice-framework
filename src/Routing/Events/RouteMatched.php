<?php

namespace CrCms\Microservice\Routing\Events;

use CrCms\Microservice\Routing\Route;
use CrCms\Microservice\Server\Contracts\ServiceContract;

class RouteMatched
{
    /**
     * The route instance.
     *
     * @var Route
     */
    public $route;

    /**
     * @var ServiceContract
     */
    public $service;

    /**
     * RouteMatched constructor.
     * @param Route $route
     * @param ServiceContract $service
     */
    public function __construct(Route $route, ServiceContract $service)
    {
        $this->route = $route;
        $this->service = $service;
    }
}

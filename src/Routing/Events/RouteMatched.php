<?php

namespace CrCms\Microservice\Routing\Events;

use CrCms\Foundation\MicroService\Contracts\ServiceContract;
use CrCms\Microservice\Routing\Route;

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

<?php

namespace CrCms\Microservice\Routing\Events;

use CrCms\Microservice\Routing\Route;
use CrCms\Microservice\Server\Contracts\RequestContract;

/**
 * Class RouteMatched.
 */
class RouteMatched
{
    /**
     * The route instance.
     *
     * @var Route
     */
    public $route;

    /**
     * @var RequestContract
     */
    public $request;

    /**
     * RouteMatched constructor.
     *
     * @param Route           $route
     * @param RequestContract $request
     */
    public function __construct(Route $route, RequestContract $request)
    {
        $this->route = $route;
        $this->request = $request;
    }
}

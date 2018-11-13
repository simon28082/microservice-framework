<?php

namespace CrCms\Microservice\Routing;

use BadMethodCallException;
use CrCms\Foundation\Helpers\InstanceConcern;
use CrCms\Microservice\Server\ResponseResource;
use InvalidArgumentException;

abstract class Controller
{
    use InstanceConcern {
        InstanceConcern::__get as __instanceGet;
    }

    /**
     * The middleware registered on the controller.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Register middleware on the controller.
     *
     * @param  array|string|\Closure $middleware
     * @param  array $options
     * @return void
     */
    public function middleware($middleware, array $options = []): void
    {
        foreach ((array)$middleware as $m) {
            $this->middleware[] = [
                'middleware' => $m,
                'options' => $options,
            ];
        }
    }

    /**
     * Get the middleware assigned to the controller.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * @return ResponseResource
     */
    protected function response(): ResponseResource
    {
        return $this->app->make(ResponseResource::class);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($name === 'response') {
            return $this->response();
        }

        if ((bool)$instance = $this->__instanceGet($name)) {
            return $instance;
        }

        throw new InvalidArgumentException("Property not found [{$name}]");
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}

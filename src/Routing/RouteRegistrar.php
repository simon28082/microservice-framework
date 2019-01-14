<?php

namespace CrCms\Microservice\Routing;

use Closure;
use BadMethodCallException;
use InvalidArgumentException;

/**
 * @method \CrCms\Microservice\Routing\Route single(string $name, \Closure | array | string | null $action = null)
 * @method \CrCms\Microservice\Routing\Route register(string $name, \Closure | array | string | null $action = null)
 * @method \CrCms\Microservice\Routing\Route multiple(string $name, \Closure | array | string | null $action = null)
 * @method \CrCms\Microservice\Routing\RouteRegistrar middleware(array | string | null $middleware)
 * @method \CrCms\Microservice\Routing\RouteRegistrar namespace(string $value)
 */
class RouteRegistrar
{
    /**
     * The router instance.
     *
     * @var \CrCms\Microservice\Routing\Router
     */
    protected $router;

    /**
     * The attributes to pass on to the router.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The methods to dynamically pass through to the router.
     *
     * @var array
     */
    protected $passthru = [
        'single', 'register', 'multiple',
    ];

    /**
     * The attributes that can be set through this class.
     *
     * @var array
     */
    protected $allowedAttributes = [
        'middleware', 'namespace', //'options'
    ];

    /**
     * Create a new route registrar instance.
     *
     * @param \CrCms\Microservice\Routing\Router $router
     *
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Set the value for a given attribute.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function attribute($key, $value)
    {
        if (! in_array($key, $this->allowedAttributes)) {
            throw new InvalidArgumentException("Attribute [{$key}] does not exist.");
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    public function group($callback)
    {
        $this->router->group($this->attributes, $callback);
    }

    /**
     * Register a new route with the router.
     *
     * @param string $method
     * @param string $name
     * @param null   $action
     * @param array  $options
     *
     * @return mixed
     */
    protected function registerRoute($method, $name, $action = null, array $options = [])
    {
        if (! is_array($action)) {
            $action = array_merge($this->attributes, $action ? ['uses' => $action] : []);
        }

        return $this->router->{$method}($name, $this->compileAction($action), $options);
    }

    /**
     * Compile the action into an array including the attributes.
     *
     * @param \Closure|array|string|null $action
     *
     * @return array
     */
    protected function compileAction($action)
    {
        if (is_null($action)) {
            return $this->attributes;
        }

        if (is_string($action) || $action instanceof Closure) {
            $action = ['uses' => $action];
        }

        return array_merge($this->attributes, $action);
    }

    /**
     * Dynamically handle calls into the route registrar.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return \CrCms\Microservice\Routing\Route|$this
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, $this->passthru)) {
            return $this->registerRoute($method, ...$parameters);
        }

        if (in_array($method, $this->allowedAttributes)) {
            if ($method === 'middleware') {
                return $this->attribute($method, is_array($parameters[0]) ? $parameters[0] : $parameters);
            }

            return $this->attribute($method, $parameters[0]);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}

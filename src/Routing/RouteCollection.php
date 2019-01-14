<?php

namespace CrCms\Microservice\Routing;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use Illuminate\Support\Arr;
use UnexpectedValueException;
use CrCms\Microservice\Server\Contracts\RequestContract;

/**
 * Class RouteCollection.
 */
class RouteCollection implements Countable, IteratorAggregate
{
    /**
     * An array of the routes keyed by method.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * An flattened array of all of the routes.
     *
     * @var array
     */
    protected $allRoutes = [];

    /**
     * A look-up table of routes by their names.
     *
     * @var array
     */
    protected $nameList = [];

    /**
     * A look-up table of routes by controller action.
     *
     * @var array
     */
    protected $actionList = [];

    /**
     * Add a Route instance to the collection.
     *
     * @param \CrCms\Microservice\Routing\Route $route
     *
     * @return \CrCms\Microservice\Routing\Route
     */
    public function add(Route $route)
    {
        $this->addToCollections($route);

        $this->addLookups($route);

        return $route;
    }

    /**
     * Add the given route to the arrays of routes.
     *
     * @param \CrCms\Microservice\Routing\Route $route
     *
     * @return void
     */
    protected function addToCollections($route)
    {
        $this->allRoutes[$route->mark()] = $route;
        $this->routes[$route->mark()] = $route;
    }

    /**
     * Add the route to any look-up tables if necessary.
     *
     * @param \CrCms\Microservice\Routing\Route $route
     *
     * @return void
     */
    protected function addLookups($route)
    {
        // If the route has a name, we will add it to the name look-up table so that we
        // will quickly be able to find any route associate with a name and not have
        // to iterate through every route every time we need to perform a look-up.
        if ($name = $route->mark()) {
            $this->nameList[$name] = $route;
        }

        // When the route is routing to a controller we will also store the action that
        // is used by the route. This will let us reverse route to controllers while
        // processing a request and easily generate URLs to the given controllers.
        $action = $route->getAction();

        if (isset($action['controller'])) {
            $this->addToActionList($action, $route);
        }
    }

    /**
     * Add a route to the controller action dictionary.
     *
     * @param array                             $action
     * @param \CrCms\Microservice\Routing\Route $route
     *
     * @return void
     */
    protected function addToActionList($action, $route)
    {
        $this->actionList[trim($action['controller'], '\\')] = $route;
    }

    /**
     * Refresh the name look-up table.
     *
     * This is done in case any names are fluently defined or if routes are overwritten.
     *
     * @return void
     */
    public function refreshNameLookups()
    {
        $this->nameList = [];

        foreach ($this->allRoutes as $route) {
            if ($route->mark()) {
                $this->nameList[$route->mark()] = $route;
            }
        }
    }

    /**
     * Refresh the action look-up table.
     *
     * This is done in case any actions are overwritten with new controllers.
     *
     * @return void
     */
    public function refreshActionLookups()
    {
        $this->actionList = [];

        foreach ($this->allRoutes as $route) {
            if (isset($route->getAction()['controller'])) {
                $this->addToActionList($route->getAction(), $route);
            }
        }
    }

    /**
     * @param RequestContract $request
     *
     * @return array
     */
    public function match(RequestContract $request)
    {
        $route = $this->get($request->currentCall());

        if (is_null($route)) {
            throw new UnexpectedValueException("The route[{$request->currentCall()}] not found");
        }

        return $route;
    }

    /**
     * Get routes from the collection by method.
     *
     * @param string|null $method
     *
     * @return array
     */
    public function get($method = null)
    {
        return is_null($method) ? $this->getRoutes() : Arr::get($this->routes, $method);
    }

    /**
     * Determine if the route collection contains a given named route.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasNamedRoute($name)
    {
        return ! is_null($this->getByName($name));
    }

    /**
     * Get a route instance by its name.
     *
     * @param string $name
     *
     * @return \CrCms\Microservice\Routing\Route|null
     */
    public function getByName($name)
    {
        return $this->nameList[$name] ?? null;
    }

    /**
     * Get a route instance by its controller action.
     *
     * @param string $action
     *
     * @return \CrCms\Microservice\Routing\Route|null
     */
    public function getByAction($action)
    {
        return $this->actionList[$action] ?? null;
    }

    /**
     * Get all of the routes in the collection.
     *
     * @return array
     */
    public function getRoutes()
    {
        return array_values($this->allRoutes);
    }

    /**
     * Get all of the routes keyed by their HTTP verb / method.
     *
     * @return array
     */
    public function getRoutesByMethod()
    {
        return $this->routes;
    }

    /**
     * Get all of the routes keyed by their name.
     *
     * @return array
     */
    public function getRoutesByName()
    {
        return $this->nameList;
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getRoutes());
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->getRoutes());
    }
}

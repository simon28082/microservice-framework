<?php

namespace CrCms\Microservice\Server\Http;

use CrCms\Microservice\Routing\Route;
use Illuminate\Http\Request as BaseRequest;
use Illuminate\Contracts\Container\Container;
use CrCms\Microservice\Server\Contracts\RequestContract;

/**
 * Class Request.
 */
class Request implements RequestContract
{
    /**
     * @var BaseRequest
     */
    protected $request;

    /**
     * @var Route
     */
    protected $route;

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $call;

    /**
     * Request constructor.
     *
     * @param BaseRequest $request
     */
    public function __construct(Container $app, BaseRequest $request)
    {
        $this->app = $app;
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function currentCall(): string
    {
        return $this->call;
    }

    /**
     * @param string $call
     *
     * @return RequestContract
     */
    public function setCurrentCall(string $call): RequestContract
    {
        $this->call = $call;

        return $this;
    }

    /**
     * @param Route $route
     *
     * @return RequestContract
     */
    public function setRoute(Route $route): RequestContract
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function rawData()
    {
        //running in swoole
        if ($this->app->has('server')) {
            return $this->app->make('server')->request->getSwooleRequest()->rawContent();
        } else {
            return file_get_contents('php://input');
        }
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->data ?? [];
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->request->method();
    }

    /**
     * @param null $key
     * @param null $default
     *
     * @return array|null|string
     */
    public function input($key = null, $default = null)
    {
        return data_get($this->data ?? [], $key, $default);
    }

    /**
     * @param array $data
     *
     * @return RequestContract
     */
    public function setData(array $data): RequestContract
    {
        $this->data = $data;

        return $this;
    }
}

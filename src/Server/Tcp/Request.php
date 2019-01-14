<?php

namespace CrCms\Microservice\Server\Tcp;

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
    protected $rawData;

    /**
     * Request constructor.
     *
     * @param BaseRequest $request
     */
    public function __construct(Container $app, string $rawData)
    {
        $this->app = $app;
        $this->rawData = $rawData;
    }

    /**
     * @return string
     */
    public function currentCall(): string
    {
        return $this->request->input('call');
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
        return $this->rawData;
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

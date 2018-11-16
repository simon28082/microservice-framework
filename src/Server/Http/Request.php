<?php

namespace CrCms\Microservice\Server\Http;

use CrCms\Microservice\Routing\Route;
use CrCms\Microservice\Server\Contracts\RequestContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request as BaseRequest;

/**
 * Class Request
 * @package CrCms\Foundation\MicroService\Http
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
     * Request constructor.
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
        return $this->request->input('call');
    }

    /**
     * @param Route $route
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
        return file_get_contents('php://input');
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
     * @return array|null|string
     */
    public function input($key = null, $default = null)
    {
        return data_get($this->data ?? [], $key, $default);
    }

    /**
     * @param array $data
     * @return RequestContract
     */
    public function setData(array $data): RequestContract
    {
        $this->data = $data;
        return $this;
    }
}
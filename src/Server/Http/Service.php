<?php

namespace CrCms\Microservice\Server\Http;

use CrCms\Microservice\Server\Contracts\ExceptionHandlerContract;
use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Contracts\ResponseContract;
use CrCms\Microservice\Routing\Route;
use CrCms\Microservice\Server\Contracts\ServiceContract;
use CrCms\Microservice\Server\Http\Exception\ExceptionHandler;
use Illuminate\Contracts\Container\Container;

/**
 * Class Service
 * @package CrCms\Foundation\MicroService\Http
 */
class Service implements ServiceContract
{
    /**
     * @var
     */
    protected $request;

    /**
     * @var
     */
    protected $response;

    /**
     * @var
     */
    protected $route;

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var
     */
    protected $indexes;

    /**
     * Service constructor.
     * @param Container $app
     * @param RequestContract $request
     */
    public function __construct(Container $app, RequestContract $request)
    {
        $this->app = $app;
        $this->setRequest($request);
        $this->baseBinding();
    }

    /**
     * @return void
     */
    public function baseBinding(): void
    {
        $this->app->bind(RequestContract::class, function ($app) {
            return $this->request;
        });

        $this->app->bind(ResponseContract::class, function () {
            return $this->response;
        });
    }

    /**
     * @param Route $route
     * @return ServiceContract
     */
    public function setRoute(Route $route): ServiceContract
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
     * @return bool
     * @throws \Exception
     */
    public function certification(): bool
    {
        $token = $this->request->headers->get('X-CRCMS-Microservice-Hash');
        $hash = hash_hmac('ripemd256', serialize($this->request->all()), config('ms.secret'));
        if ($token !== $hash) {
            //throw new AccessDeniedHttpException("Microservice Hash error:" . strval($token));
            throw new \Exception("Microservice Hash error:" . strval($token));
            return false;
        }

        return true;
    }

    /**
     * @param RequestContract $request
     * @return ServiceContract
     */
    public function setRequest(RequestContract $request): ServiceContract
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param ResponseContract $response
     * @return ServiceContract
     */
    public function setResponse(ResponseContract $response): ServiceContract
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return RequestContract
     */
    public function getRequest(): RequestContract
    {
        return $this->request;
    }

    /**
     * @return ResponseContract
     */
    public function getResponse(): ResponseContract
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->request->get('method');
    }

//    public function indexes(?string $key = null)
//    {
//        if (is_null($this->indexes)) {
//            $method = explode('.', $this->request->get('method'));
//            $this->indexes = ['name' => $method[0], 'method' => $method[1] ?? null];
//        }
//
//        return $this->indexes[$key];
//    }

    /**
     * @param mixed $response
     * @return ServiceContract
     */
    public function createResponse($response): ServiceContract
    {
        $this->response = Response::createResponse($response);
        return $this;
    }

    /**
     * @param RequestContract $request
     * @param ResponseContract $response
     * @return ResponseContract
     */
    public static function toResponse(RequestContract $request, ResponseContract $response): ResponseContract
    {
        return $response->prepare($request);
    }
}
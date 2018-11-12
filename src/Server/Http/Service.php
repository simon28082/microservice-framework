<?php

namespace CrCms\Microservice\Server\Http;

//namespace CrCms\Microservice\Server\Http\Exception\ExceptionHandler;
use CrCms\Microservice\Server\Contracts\ExceptionHandlerContract;
use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Contracts\ResponseContract;
use CrCms\Microservice\Routing\Route;
use CrCms\Microservice\Server\Contracts\ServiceContract;
use CrCms\Microservice\Server\Http\Exception\ExceptionHandler;
use Illuminate\Contracts\Container\Container;
//use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use CrCms\Microservice\Server\Http\Response as HttpResponse;

/**
 * Class Service
 * @package CrCms\Foundation\MicroService\Http
 */
class Service implements ServiceContract
{
    protected $request;

    protected $response;

    protected $route;

    protected $app;

    protected $indexes;

    public function __construct(Container $app, RequestContract $request)
    {
        $this->app = $app;
        $this->baseBinding();
        $this->setRequest($request);
        $this->app->bind(RequestContract::class, function ($app) {
            return $this->request;
        });
        $this->app->bind(ResponseContract::class, function ($app) {
            return $this->response;
        });
        //$this->setResponse(new Response());
        //$this->app->bind(ResponseContract::class, Response::class);
    }

    public function createResponse($response): ServiceContract
    {
        $this->response = Response::createResponse($response);
        return $this;
    }

    public function baseBinding(): void
    {
        $this->app->singleton(
            ExceptionHandlerContract::class,
            ExceptionHandler::class
        );


//        $this->app->bind(ResponseContract::class, function () {
//            return $this->response;
//        });
    }

//    public static function createResponse($response)
//    {
//
//    }

    public function setRoute(Route $route): ServiceContract
    {
        $this->route = $route;
        return $this;
    }

    public function getRoute(): Route
    {
        return $this->route;
    }

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

    public function setRequest(RequestContract $request): ServiceContract
    {
        $this->request = $request;
        return $this;
    }

    public function setResponse(ResponseContract $response): ServiceContract
    {
        $this->response = $response;
        return $this;
    }


    public function getRequest(): RequestContract
    {
        return $this->request;
    }

    public function getResponse(): ResponseContract
    {
        return $this->response;
    }

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

    public static function toResponse(RequestContract $request, ResponseContract $response): ResponseContract
    {
        return $response->prepare($request);
    }
}
<?php

namespace CrCms\Microservice\Server\Http;

use CrCms\Foundation\Transporters\AbstractDataProvider;
use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Contracts\ResponseContract;
use CrCms\Microservice\Routing\Route;
use CrCms\Microservice\Server\Contracts\ServiceContract;
use CrCms\Microservice\Server\Events\ServiceHandling;
use Illuminate\Contracts\Container\Container;
use BadMethodCallException;

/**
 * Class Service
 * @package CrCms\Foundation\MicroService\Http
 */
class Service implements ServiceContract
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var AbstractDataProvider
     */
    protected $dataProvider;

    /**
     * @var
     */
    protected $route;

    /**
     * @var Container
     */
    protected $app;

    /**
     * Service constructor.
     * @param Container $app
     * @param RequestContract $request
     */
    public function __construct(Container $app, RequestContract $request)
    {
        $this->app = $app;
        $this->setRequest($request);
        $this->registerEvent();
    }

    /**
     * @param AbstractDataProvider $dataProvider
     * @return ServiceContract
     */
    public function setDataProvider(AbstractDataProvider $dataProvider): ServiceContract
    {
        $this->dataProvider = $dataProvider;
        return $this;
    }

    /**
     * @return AbstractDataProvider
     */
    public function getDataProvider(): AbstractDataProvider
    {
        return $this->dataProvider;
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
     * @return void
     */
    public function registerEvent(): void
    {
        $this->app['events']->listen(ServiceHandling::class, function (ServiceHandling $event) {
            $this->baseBinding();
            if ($event->service->getRequest()->getMethod() !== 'POST') {
                return $this->allServices();
            }
        });
    }

    /**
     * @return ResponseContract
     */
    protected function allServices(): ResponseContract
    {
        $methods = collect($this->app->make('router')->getRoutes()->get())->mapWithKeys(function (Route $route) {
            $uses = $route->getAction('uses');
            $uses = $uses instanceof \Closure ? 'Closure' : $uses;
            return [$route->mark() => $uses];
        })->toArray();
        return new Response([
            'methods' => $methods
        ], 200);
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
//        $token = $this->request->headers->get('X-CRCMS-Microservice-Hash');
//        $hash = hash_hmac('ripemd256', serialize($this->request->all()), config('ms.secret'));
//        if ($token !== $hash) {
//            //throw new AccessDeniedHttpException("Microservice Hash error:" . strval($token));
//            throw new \Exception("Microservice Hash error:" . strval($token));
//            return false;
//        }
//
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
        return $this->request->get('call');
    }

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

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->dataProvider, $name)) {
            return $this->dataProvider->{$name}(...$arguments);
        }

        throw new BadMethodCallException('Undefined method ' . get_class($this) . '::' . $name);
    }
}
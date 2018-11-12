<?php

namespace CrCms\Microservice\Server\Http;

use CrCms\Microservice\Server\Contracts\ResponseContract;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
//use Symfony\Component\HttpFoundation\Response as BaseResponse;
use CrCms\Foundation\MicroService\Contracts\ServiceContract;
use CrCms\Foundation\MicroService\Routing\Route;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\BindingRegistrar;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Illuminate\Contracts\Routing\Registrar as RegistrarContract;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class Response
 * @package CrCms\Foundation\MicroService\Http
 */
class Response extends JsonResponse implements ResponseContract
{

    public static function createResponse($response): ResponseContract
    {
        if ($response instanceof Model && $response->wasRecentlyCreated) {
            $response = new static($response, 201);
        } elseif ($response instanceof JsonResponse) {
            $response = new static($response->getData(), $response->getStatusCode(), $response->headers->all(), $response->getEncodingOptions());
        } elseif (!$response instanceof SymfonyResponse &&
            ($response instanceof Arrayable ||
                $response instanceof Jsonable ||
                $response instanceof ArrayObject ||
                $response instanceof JsonSerializable ||
                is_array($response))) {
            $response = new static($response);
        } else {
            $response = new static($response);
        }

        if ($response->getStatusCode() === Response::HTTP_NOT_MODIFIED) {
            $response->setNotModified();
        }

        return $response;
    }


    /**
     * {@inheritdoc}
     */
    public function setData($data = [])
    {
        if ($data instanceof JsonResponse) {
            $data = $data->getData();
        }

        return parent::setData($data);
    }

//    public function send(): void
//    {
//    }
//
    protected function resolveResponse()
    {
//        if ($response instanceof Responsable) {
//            $response = $response->toResponse($request);
//        }

        if ($response instanceof PsrResponseInterface) {
            $response = (new HttpFoundationFactory)->createResponse($response);
        } elseif ($response instanceof Model && $response->wasRecentlyCreated) {
            $response = new JsonResponse($response, 201);
        } elseif (!$response instanceof SymfonyResponse &&
            ($response instanceof Arrayable ||
                $response instanceof Jsonable ||
                $response instanceof ArrayObject ||
                $response instanceof JsonSerializable ||
                is_array($response))) {
            $response = new JsonResponse($response);
        } elseif (!$response instanceof SymfonyResponse) {
            $response = new Response($response);
        }

        if ($response->getStatusCode() === Response::HTTP_NOT_MODIFIED) {
            $response->setNotModified();
        }

        return $response->prepare($request);
    }
}
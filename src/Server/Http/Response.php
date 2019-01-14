<?php

namespace CrCms\Microservice\Server\Http;

use ArrayObject;
use JsonSerializable;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Jsonable;
//use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Illuminate\Contracts\Support\Arrayable;
use CrCms\Microservice\Server\Contracts\ResponseContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class Response.
 */
class Response extends JsonResponse implements ResponseContract
{
    /**
     * @param $response
     *
     * @return ResponseContract
     */
    public static function createResponse($response): ResponseContract
    {
        if ($response instanceof Model && $response->wasRecentlyCreated) {
            $response = new static($response, 201);
        } elseif ($response instanceof JsonResponse) {
            $response = new static($response->getData(), $response->getStatusCode(), $response->headers->all(), $response->getEncodingOptions());
        } elseif (! $response instanceof SymfonyResponse &&
            ($response instanceof Arrayable ||
                $response instanceof Jsonable ||
                $response instanceof ArrayObject ||
                $response instanceof JsonSerializable ||
                is_array($response))) {
            $response = new static($response);
        } else {
            $response = new static($response);
        }

        if ($response->getStatusCode() === self::HTTP_NOT_MODIFIED) {
            $response->setNotModified();
        }

        return $response->forceHeaders();
    }

    /**
     * @return Response
     */
    public function forceHeaders(): self
    {
        $headers = $this->headers;

        if ($this->isInformational() || $this->isEmpty()) {
            $this->setContent(null);
            $headers->remove('Content-Type');
            $headers->remove('Content-Length');
        } else {
            $headers->set('Content-Type', 'application/json; charset=UTF-8');
        }
        //$headers->set('Connection','keep-alive');

        // Fix protocol
        $this->setProtocolVersion('1.1');

        return $this;
    }
}

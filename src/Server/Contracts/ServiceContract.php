<?php

namespace CrCms\Microservice\Server\Contracts;

use CrCms\Microservice\Routing\Route;

/**
 * Class ServiceContract
 * @package CrCms\Foundation\MicroService\Contracts
 */
interface ServiceContract
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @param null|string $key
     * @return array|string
     */
    //public function indexes(?string $key = null);

    /**
     * @return void
     */
    public function bindKernel(): void;

    /**
     * @param Route $route
     * @return ServiceContract
     */
    public function setRoute(Route $route): ServiceContract;

    /**
     * @return Route
     */
    public function getRoute(): Route;

    /**
     * @param RequestContract $request
     * @return ServiceContract
     */
    public function setRequest(RequestContract $request): ServiceContract;

    /**
     * @return RequestContract
     */
    public function getRequest(): RequestContract;

    /**
     * @param mixed $response
     * @return ServiceContract
     */
    public function setResponse($response): ServiceContract;

    /**
     * @return ResponseContract
     */
    public function getResponse(): ResponseContract;

    /**
     * @param RequestContract $request
     * @param ResponseContract $response
     * @return ResponseContract
     */
    public static function toResponse(RequestContract $request, ResponseContract $response): ResponseContract;

    /**
     * @return string
     */
//    public static function exceptionHandler(): string;

    /**
     * @return bool
     */
    public function certification(): bool;
}
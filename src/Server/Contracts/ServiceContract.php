<?php

namespace CrCms\Microservice\Server\Contracts;

use CrCms\Foundation\Transporters\AbstractDataProvider;
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
     * @return void
     */
    public function baseBinding(): void;

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
     * @param ResponseContract $response
     * @return ServiceContract
     */
    public function setResponse(ResponseContract $response): ServiceContract;

    /**
     * @return ResponseContract
     */
    public function getResponse(): ResponseContract;

    /**
     * @param AbstractDataProvider $dataProvider
     * @return ServiceContract
     */
    public function setDataProvider(AbstractDataProvider $dataProvider): ServiceContract;

    /**
     * @return AbstractDataProvider
     */
    public function getDataProvider(): AbstractDataProvider;

    /**
     * @param mixed $response
     * @return ServiceContract
     */
    public function createResponse($response): ServiceContract;

    /**
     * @param RequestContract $request
     * @param ResponseContract $response
     * @return ResponseContract
     */
    public static function toResponse(RequestContract $request, ResponseContract $response): ResponseContract;

    /**
     * @return bool
     */
    public function certification(): bool;
}
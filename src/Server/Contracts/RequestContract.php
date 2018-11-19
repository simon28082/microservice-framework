<?php

namespace CrCms\Microservice\Server\Contracts;

use CrCms\Microservice\Routing\Route;

/**
 * Interface RequestContract
 * @package CrCms\Foundation\MicroService\Contracts
 */
interface RequestContract
{
    /**
     * @return mixed
     */
    //public static function createRequest(): RequestContract;

    /**
     * @return string
     */
    public function currentCall(): string;

    /**
     * @param string $call
     * @return RequestContract
     */
    public function setCurrentCall(string $call): RequestContract;

    /**
     * @param Route $route
     * @return RequestContract
     */
    public function setRoute(Route $route): RequestContract;

    /**
     * @return Route
     */
    public function getRoute(): Route;

    /**
     * @return mixed
     */
    public function rawData();

    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param array $data
     * @return RequestContract
     */
    public function setData(array $data): RequestContract;
}
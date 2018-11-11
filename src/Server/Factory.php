<?php

namespace CrCms\Microservice\Server;

use CrCms\Microservice\Server\Contracts\ServiceContract;
use CrCms\Microservice\Server\Http\Service as HttpService;
use CrCms\Microservice\Server\Http\Request as HttpRequest;
use Illuminate\Contracts\Container\Container;

/**
 * Class Factory
 * @package CrCms\Foundation\MicroService
 */
class Factory
{
    public static function service(Container $app, string $driver): ServiceContract
    {
        switch ($driver) {
            case 'http':
                return new HttpService($app, HttpRequest::createRequest());
        }
    }
}
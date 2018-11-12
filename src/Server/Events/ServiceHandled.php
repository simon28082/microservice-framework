<?php

namespace CrCms\Microservice\Server\Events;

use CrCms\Microservice\Server\Contracts\ServiceContract;

/**
 * Class ServiceHandled
 * @package CrCms\Microservice\Server\Events
 */
class ServiceHandled
{
    /**
     * @var ServiceContract
     */
    public $service;

    /**
     * ServiceHandled constructor.
     * @param ServiceContract $service
     */
    public function __construct(ServiceContract $service)
    {
        $this->service = $service;
    }
}
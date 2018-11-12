<?php

namespace CrCms\Microservice\Server\Events;

use CrCms\Microservice\Server\Contracts\ServiceContract;

/**
 * Class ServiceHandling
 * @package CrCms\Microservice\Server\Events
 */
class ServiceHandling
{
    /**
     * @var ServiceContract
     */
    public $service;

    /**
     * ServiceHandling constructor.
     * @param ServiceContract $service
     */
    public function __construct(ServiceContract $service)
    {
        $this->service = $service;
    }
}
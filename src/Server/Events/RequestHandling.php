<?php

namespace CrCms\Microservice\Server\Events;

use CrCms\Microservice\Server\Contracts\RequestContract;

/**
 * Class RequestHandling
 * @package CrCms\Microservice\Server\Events
 */
class RequestHandling
{
    /**
     * @var RequestContract
     */
    public $request;

    /**
     * RequestHandling constructor.
     * @param RequestContract $request
     */
    public function __construct(RequestContract $request)
    {
        $this->request = $request;
    }
}
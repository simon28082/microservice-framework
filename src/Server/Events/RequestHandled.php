<?php

namespace CrCms\Microservice\Server\Events;

use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Contracts\ResponseContract;

/**
 * Class RequestHandled.
 */
class RequestHandled
{
    /**
     * @var RequestContract
     */
    public $request;

    /**
     * @var ResponseContract
     */
    public $response;

    /**
     * RequestHandled constructor.
     *
     * @param RequestContract  $request
     * @param ResponseContract $response
     */
    public function __construct(RequestContract $request, ResponseContract $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}

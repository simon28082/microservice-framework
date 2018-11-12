<?php

namespace CrCms\Microservice\Server\Exceptions;

use CrCms\Microservice\Server\Contracts\ServiceContract;
use RuntimeException;
use Throwable;

/**
 * Class ServiceException
 * @package CrCms\Microservice\Server\Http\Exception
 */
class ServiceException extends RuntimeException
{
    protected $service;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function setService(ServiceContract $service)
    {
        $this->service = $service;
        return $this;
    }

    public function getService(): ServiceContract
    {
        return $this->service;
    }
}
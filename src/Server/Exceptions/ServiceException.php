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
    /**
     * @var ServiceContract
     */
    protected $service;

    /**
     * ServiceException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param ServiceContract $service
     * @return $this
     */
    public function setService(ServiceContract $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return ServiceContract
     */
    public function getService(): ServiceContract
    {
        return $this->service;
    }
}
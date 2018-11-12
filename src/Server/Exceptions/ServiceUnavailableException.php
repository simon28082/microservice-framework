<?php

namespace CrCms\Microservice\Server\Exceptions;

use Throwable;

class ServiceUnavailableException extends ServiceException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 503, $previous);
    }
}

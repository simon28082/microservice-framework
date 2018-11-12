<?php

namespace CrCms\Microservice\Server\Exceptions;

use Throwable;

class UnauthorizedException extends ServiceException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }
}

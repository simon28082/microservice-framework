<?php

namespace CrCms\Microservice\Server\Exceptions;

use Throwable;

/**
 * @author Steve Hutchins <hutchinsteve@gmail.com>
 */
class UnprocessableEntityException extends ServiceException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}

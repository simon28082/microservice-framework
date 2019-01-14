<?php

namespace CrCms\Microservice\Server\Exceptions;

use Throwable;

/**
 * Class BadRequestException.
 */
class BadRequestException extends ServiceException
{
    /**
     * BadRequestException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }
}

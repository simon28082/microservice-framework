<?php

namespace CrCms\Microservice\Server\Exceptions;

use Throwable;

/**
 * Class NotAcceptableException.
 */
class NotAcceptableException extends ServiceException
{
    /**
     * NotAcceptableException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 406, $previous);
    }
}

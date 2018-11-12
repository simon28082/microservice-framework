<?php

namespace CrCms\Microservice\Server\Exceptions;

use Throwable;

/**
 * Class MethodNotAllowedException
 * @package CrCms\Microservice\Server\Exceptions
 */
class MethodNotAllowedException extends ServiceException
{
    /**
     * MethodNotAllowedException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 405, $previous);
    }
}

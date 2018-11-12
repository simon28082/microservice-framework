<?php

namespace CrCms\Microservice\Server\Exceptions;

use Throwable;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class NotFoundException extends ServiceException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}

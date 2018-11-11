<?php

namespace CrCms\Microservice\Server\Contracts;

use Exception;

/**
 * Interface ExceptionHandlerContract
 * @package CrCms\Microservice\Microservice\Contracts
 */
interface ExceptionHandlerContract
{
    /**
     * @param Exception $e
     * @return mixed
     */
    public function report(Exception $e);

    /**
     * @param ServiceContract $service
     * @param Exception $e
     * @return mixed
     */
    public function render(ServiceContract $service, Exception $e);
}
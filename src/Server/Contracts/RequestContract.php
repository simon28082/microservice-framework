<?php

namespace CrCms\Microservice\Server\Contracts;

/**
 * Interface RequestContract
 * @package CrCms\Foundation\MicroService\Contracts
 */
interface RequestContract
{
    /**
     * @return mixed
     */
    public static function createRequest(): RequestContract;
}
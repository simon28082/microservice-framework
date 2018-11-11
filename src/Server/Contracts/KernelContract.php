<?php

namespace CrCms\Microservice\Server\Contracts;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;

/**
 * Interface KernelContract
 * @package CrCms\Foundation\Rpc\Server\Contracts
 */
interface KernelContract
{
    /**
     * @return void
     */
    public function bootstrap(): void;

    /**
     * @param ServiceContract $service
     * @return ResponseContract
     */
    public function handle(ServiceContract $service): ResponseContract;

    /**
     * @param ServiceContract $service
     * @return mixed
     */
    public function terminate(ServiceContract $service);

    /**
     * @return ApplicationContract
     */
    public function getApplication(): ApplicationContract;
}
<?php

namespace CrCms\Microservice\Server\Contracts;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;

/**
 * Interface KernelContract.
 */
interface KernelContract
{
    /**
     * @return void
     */
    public function bootstrap(): void;

    /**
     * @param RequestContract $request
     *
     * @return ResponseContract
     */
    public function handle(RequestContract $request): ResponseContract;

    /**
     * @param RequestContract  $request
     * @param ResponseContract $response
     *
     * @return mixed
     */
    public function terminate(RequestContract $request, ResponseContract $response);

    /**
     * @return ApplicationContract
     */
    public function getApplication(): ApplicationContract;
}

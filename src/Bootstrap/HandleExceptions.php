<?php

namespace CrCms\Microservice\Bootstrap;

use Exception;
use CrCms\Microservice\Server\Contracts\ResponseContract;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Illuminate\Foundation\Bootstrap\HandleExceptions as BaseHandleExceptions;

class HandleExceptions extends BaseHandleExceptions
{
    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function handleException($e)
    {
        if (! $e instanceof Exception) {
            $e = new FatalThrowableError($e);
        }

        try {
            $this->getExceptionHandler()->report($e);
        } catch (Exception $e) {
            //
        }

        if ($this->app->runningInConsole()) {
            $this->renderForConsole($e);
        } else {
            $this->renderApplication($e);
        }
    }

    /**
     * Render an exception as an HTTP response and send it.
     *
     * @param \Exception $e
     *
     * @return void
     */
    protected function renderApplication(Exception $e)
    {
        $response = $this->getExceptionHandler()->render($this->app['request'], $e);
        if ($response instanceof ResponseContract) {
            $response->setData($this->pack($response->getData(true)));
        }
        $response->send();
    }

    /**
     * @param array $messages
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return array
     */
    protected function pack(array $messages): array
    {
        return ['data' => $this->app->make('server.packer')->pack($messages, config('app.secret_status'))];
    }
}

<?php

namespace CrCms\Microservice\Console;

use CrCms\Microservice\Bootstrap\Start;
use CrCms\Server\Drivers\Laravel\Contracts\ApplicationContract;
use CrCms\Server\Drivers\Laravel\Application;
use Illuminate\Contracts\Container\Container;

class ServerApplication extends Application implements ApplicationContract
{
    /**
     * createApplication
     *
     * @return Container
     */
    protected function createApplication(): Container
    {
        return Start::instance()->bootstrap()->getApplication();
    }

    /**
     * bootstrap
     *
     * @param Container $app
     * @return void
     */
    protected function bootstrap($app): void
    {
        $app->bootstrapWith([
            \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
            \CrCms\Microservice\Bootstrap\LoadConfiguration::class,
            \CrCms\Microservice\Bootstrap\HandleExceptions::class,
            \CrCms\Microservice\Bootstrap\RegisterFacades::class,
            //\Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
            \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
            \Illuminate\Foundation\Bootstrap\BootProviders::class,
        ]);
    }
}

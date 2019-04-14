<?php

namespace CrCms\Microservice\Console\Commands;

use CrCms\Microservice\Bootstrap\Start;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Foundation\Console\ConfigCacheCommand as BaseConfigCacheCommand;

class ConfigCacheCommand extends BaseConfigCacheCommand
{
    /**
     * Boot a fresh copy of the application configuration.
     *
     * @return array
     */
    protected function getFreshConfiguration()
    {
        //$app = require $this->laravel->bootstrapPath().'/app.php';
        $app = Start::instance()->bootstrap()->getApplication();

        $app->useStoragePath($this->laravel->storagePath());

        $app->make(ConsoleKernelContract::class)->bootstrap();

        return $app['config']->all();
    }
}

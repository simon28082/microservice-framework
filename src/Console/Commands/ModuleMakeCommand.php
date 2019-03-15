<?php

namespace CrCms\Microservice\Console\Commands;

use CrCms\Foundation\Commands\ModuleMakeCommand as BaseModuleMakeCommand;

/**
 * Class ModuleMakeCommand
 * @package CrCms\Microservice\Console\Commands
 */
class ModuleMakeCommand extends BaseModuleMakeCommand
{
    /**
     * @param string $name
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createRoutes(string $name): void
    {
        $webFile = base_path('modules/'.$name.'/Routes/service.php');
        if (!$this->files->exists($webFile)) {
            $this->files->put($webFile, $this->files->get(__DIR__.'/stubs/service-route.stub'));
        }
    }
}

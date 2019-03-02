<?php

namespace CrCms\Microservice\Console;

use CrCms\Microservice\Console\Commands\ModuleMakeCommand;
use CrCms\Microservice\Console\Commands\InitializeMakeCommand;
use Illuminate\Foundation\Providers\ArtisanServiceProvider as BaseArtisanServiceProvider;

class ArtisanServiceProvider extends BaseArtisanServiceProvider
{
    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        parent::__construct($app);

        $this->devCommands['InitializeMake'] = 'command.initialize.make';
        $this->devCommands['ModuleMake'] = 'command.module.make';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands(array_merge(
            $this->commands, $this->devCommands
        ));
    }

    /**
     * register Initialize.
     *
     * @return void
     */
    protected function registerInitializeMakeCommand()
    {
        $this->app->singleton('command.initialize.make', function ($app) {
            return new InitializeMakeCommand($app['files']);
        });
    }

    /**
     * register module.
     *
     * @return void
     */
    protected function registerModuleMakeCommand()
    {
        $this->app->singleton('command.module.make', function ($app) {
            return new ModuleMakeCommand($app['files']);
        });
    }
}

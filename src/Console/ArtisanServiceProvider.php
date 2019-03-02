<?php

namespace CrCms\Microservice\Console;

use CrCms\Microservice\Console\Commands\ConfigCacheCommand;
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

        $this->removeLaravelCommands();

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

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerConfigCacheCommand()
    {
        $this->app->singleton('command.config.cache', function ($app) {
            return new ConfigCacheCommand($app['files']);
        });
    }


    /**
     * removeLaravelCommands
     *
     * @return void
     */
    protected function removeLaravelCommands(): void
    {
        $devCommands = ['AuthMake', 'ControllerMake', 'MiddlewareMake',
            'NotificationTable', 'ExceptionMake', 'PolicyMake', 'SessionTable',
            'Optimize',
            'OptimizeClear',
        ];
        array_map(function ($item) {
            unset($this->devCommands[$item]);
        }, $devCommands);

        $commands = ['ClearResets', 'RouteCache',
            'RouteClear',
            'RouteList', 'Preset', 'ViewCache',
            'ViewClear', 'Serve',];
        array_map(function ($item) {
            unset($this->commands[$item]);
        }, $commands);
    }
}

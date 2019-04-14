<?php

namespace CrCms\Microservice\Console;

use Illuminate\Console\Scheduling\Schedule;
use CrCms\Microservice\Console\Application as Artisan;
use Illuminate\Foundation\Console\Kernel as BaseKernel;
use Illuminate\Contracts\Console\Kernel as KernelContract;

class Kernel extends BaseKernel implements KernelContract
{
    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \CrCms\Microservice\Bootstrap\LoadConfiguration::class,
        \CrCms\Microservice\Bootstrap\HandleExceptions::class,
        \CrCms\Microservice\Bootstrap\RegisterFacades::class,
        //\Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedules = $this->app['config']->get('mount.schedules', []);
        foreach ($schedules as $scheduleCommand) {
            (new $scheduleCommand())->handle($schedule);
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $commands = $this->app['config']->get('mount.commands', []);
        if ($commands) {
            Artisan::starting(function ($artisan) use ($commands) {
                $artisan->resolveCommands($commands);
            });
        }
    }

    /**
     * getArtisan.
     *
     * @return Application|\Illuminate\Console\Application
     */
    public function getArtisan()
    {
        if (is_null($this->artisan)) {
            return $this->artisan = (new Artisan($this->app, $this->events, $this->app->msVersion()))
                ->resolveCommands($this->commands);
        }

        return $this->artisan;
    }
}

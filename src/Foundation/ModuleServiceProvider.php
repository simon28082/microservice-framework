<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2019-01-19 00:06
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2019 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Foundation;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class ModuleServiceProvider
 * @package CrCms\Microservice\Foundation
 */
class ModuleServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->scanLoadMigrations();
        $this->scanLoadTranslations();
        $this->scanLoadRoutes();
        $this->scanCommands();
        $this->scanLoadSchedules();
    }

    /**
     * @return void
     */
    public function register(): void
    {

    }

    /**
     *
     */
    protected function scanLoadMigrations(): void
    {
        /* @var SplFileInfo $directory */
        foreach (Finder::create()->directories()->name('Migrations')->in($this->app->modulePath()) as $directory) {
            $this->loadMigrationsFrom($directory->getPathname());
        }
    }

    /**
     * @return void
     */
    protected function scanLoadTranslations(): void
    {
        //$this->loadTranslationsFrom(realpath(__DIR__.'/../../resources/lang'), '*');

        /* @var SplFileInfo $directory */
        foreach (Finder::create()->directories()->name('Translations')->in($this->app->modulePath()) as $directory) {
            $this->loadTranslationsFrom($directory->getPathname(), Str::kebab($directory->getRelativePath()));
        }

        $this->app->afterResolving('translation.loader', function ($app) {
            $app['translation.loader']->addJsonPath(realpath(__DIR__.'/../../resources/lang'));
        });
    }

    /**
     * @return void
     */
    protected function scanLoadRoutes(): void
    {
        /* @var SplFileInfo $directory */
        /* @var SplFileInfo $file */
        foreach (Finder::create()->directories()->name('Routes')->in($this->app->modulePath()) as $directory) {
            foreach (Finder::create()->files()->name('*.php')->in($directory->getPathname()) as $file) {
                require $file->getPathname();
            }
        }
    }

    /**
     * @return void
     */
    protected function scanCommands(): void
    {
        /* @var SplFileInfo $file */
        foreach (Finder::create()->files()->name('*Command.php')->in($this->app->modulePath()) as $file) {
            $class = $this->fileToClass($file);
            if ($class) {
                $this->commands($class);
            }
        }
    }

    /**
     * @return void
     */
    protected function scanLoadSchedules()
    {
        /* @var SplFileInfo $file */
        $schedule = $this->app->make(Schedule::class);
        foreach (Finder::create()->files()->name('*Schedule.php')->in($this->app->modulePath()) as $file) {
            $class = $this->fileToClass($file);
            if ($class) {
                $this->app->make($class)->handle($schedule);
            }
        }
    }

    /**
     * @param SplFileInfo $file
     *
     * @return string|null
     */
    protected function fileToClass(SplFileInfo $file)
    {
        preg_match('/.*namespace\s([^\;]+)?/i', $file->getContents(), $match);

        if (isset($match[1])) {
            $class = $match[1].'\\'.$file->getBasename('.php');
            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }
}
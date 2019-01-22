<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2019-01-19 21:22
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2019 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Tests\Providers;

use CrCms\Microservice\Bootstrap\LoadConfiguration;
use CrCms\Microservice\Console\Commands\InitializeMakeCommand;
use CrCms\Microservice\Console\Kernel;
use CrCms\Microservice\Foundation\Application;
use CrCms\Microservice\Foundation\MountServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Translation\Translator;
use PHPUnit\Framework\TestCase;

/**
 * Class MountServiceProviderTest
 * @package CrCms\Microservice\Tests
 */
class MountServiceProviderTest extends TestCase
{
    /**
     * @return void
     */
    protected function autoCreateDirs(Application $app, $namespace): void
    {
        foreach ([
                     $app->modulePath($namespace.'/Schedules'),
                     $app->modulePath($namespace.'/Commands'),
                     $app->modulePath($namespace.'/Events'),
                     $app->modulePath($namespace.'/Exceptions'),
                     $app->modulePath($namespace.'/Handlers'),
                     $app->modulePath($namespace.'/Tasks'),
                     $app->modulePath($namespace.'/Jobs'),
                     $app->modulePath($namespace.'/Listeners'),
                     $app->modulePath($namespace.'/Models'),
                     $app->modulePath($namespace.'/Providers'),
                     $app->modulePath($namespace.'/Repositories'),
                     $app->modulePath($namespace.'/Middleware'),
                     $app->modulePath($namespace.'/DataProviders'),
                     $app->modulePath($namespace.'/Controllers'),
                     $app->modulePath($namespace.'/Resources'),
                     $app->modulePath($namespace.'/Translations'),
                     $app->modulePath($namespace.'/Database/Factories'),
                     $app->modulePath($namespace.'/Database/Migrations'),
                     $app->modulePath($namespace.'/Database/Seeds'),
                 ] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 755, true);
            }
        }

        $this->copyCommandClass($app->modulePath($namespace.'/Commands'));
        copy(__DIR__.'/stub/lang.php', $app->modulePath($namespace.'/Translations/en/lang.php'));
        copy(__DIR__.'/stub/TestSchedule.php', $app->modulePath($namespace.'/Schedules/TestSchedule.php'));
    }

    public function testAbc()
    {
        $app = forkApp();

        (new LoadConfiguration())->bootstrap($app);

        $app->registerConfiguredProviders();

        $app->boot();

        $app->make(Kernel::class);

        $app->register(MigrationServiceProvider::class);
        //TranslationServiceProvider must reload before MountServiceProvider
        $app->register(TranslationServiceProvider::class,true);

        $this->autoCreateDirs($app, 'Support');
        $serviceProvider = new MountServiceProvider($app);
        $serviceProvider->register();
        $serviceProvider->boot();


        $this->assertEquals(true, in_array(
            $app->modulePath('Support/Database/Migrations'),
            $app->make('migrator')->paths()));

        $this->assertEquals('testing', $app->make('translator')->trans('support::lang.test'));


        $this->assertEquals(1,$this->count($app->make(Schedule::class)->events()));

    }

    protected function copyCommandClass($path)
    {
        copy(__DIR__.'/stub/TestCommand.php', $path.DIRECTORY_SEPARATOR.'TestCommand.php');
    }
}
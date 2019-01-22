<?php

namespace CrCms\Microservice\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Class InitializeMakeCommand.
 */
class InitializeMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'initialize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize the system directory and sample files';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var array
     */
    protected $modules = ['storage',];

    /**
     * AutoCreateStorageCommand constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->files = $filesystem;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->modules as $module) {
            call_user_func([$this, 'create' . ucfirst($module)]);
        }

        $this->info('Initialize completed');
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createResource()
    {
        if (!$this->files->exists(resource_path())) {
            $this->autoCreateDirs([resource_path()]);
        }

        if (!$this->files->exists(app()->langPath())) {
            $langPath = app()->langPath();
            $localPath = $langPath . '/' . config('app.locale');
            $this->autoCreateDirs([
                $langPath,
                $localPath,
            ]);

            $this->files->put($localPath . '/pagination.php', $this->files->get(__DIR__ . '/stubs/lang/' . config('app.locale') . '/pagination.stub'));
            $this->files->put($localPath . '/validation.php', $this->files->get(__DIR__ . '/stubs/lang/' . config('app.locale') . '/validation.stub'));
        }
    }

    /**
     * @return void
     */
    /*protected function createDatabase(): void
    {
        if (!$this->files->exists(database_path())) {
            $this->autoCreateDirs([
                database_path('factories'),
                database_path('migrations'),
                database_path('seeds'),
            ]);

            $this->files->put(database_path('factories/UserFactory.php'), $this->files->get(__DIR__ . '/stubs/factory.stub'));
            $this->files->put(database_path('migrations/2014_10_12_000000_create_users_table.php'), $this->files->get(__DIR__ . '/stubs/migration.stub'));
            $this->files->put(database_path('seeds/DatabaseSeeder.php'), $this->files->get(__DIR__ . '/stubs/seed.stub'));
        }
    }*/

    /**
     * @return void
     */
    protected function createConfig(): void
    {
        if (!$this->files->exists(base_path('config'))) {
            $this->autoCreateDirs([base_path('config')]);
            $this->files->copyDirectory(__DIR__ . '/../../../config', base_path('config'));
        }
    }

    /**
     * @return void
     */
    protected function createStorage(): void
    {
        $this->autoCreateDirs([
            'runCachePath' => storage_path('run-cache'),
            'cachePath' => config('cache.stores.file.path'),
            'logPath' => storage_path('logs'),
            'appPublicPath' => storage_path('app/public'),
            'testingPath' => storage_path('framework/testing'),
            'viewPath' => storage_path('framework/views'),
        ]);

        $gitignore = storage_path('.gitignore');
        if (!$this->files->exists($gitignore)) {
            $this->files->put(storage_path('.gitignore'), '*');
        }
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return void
     */
    /*protected function createRoutes(): void
    {
        $routePath = base_path('routes');
        if (!$this->files->exists($routePath)) {
            $this->files->makeDirectory($routePath, 0755, true);
        }

        $webFile = base_path('routes/service.php');
        if (!$this->files->exists($webFile)) {
            $this->files->put($webFile, $this->files->get(__DIR__ . '/stubs/service-route.stub'));
        }
    }*/

    /**
     * @param array $dirs
     *
     * @return void
     */
    protected function autoCreateDirs(array $dirs): void
    {
        foreach ($dirs as $dir) {
            if (!$this->files->exists($dir) && !empty($dir)) {
                $this->files->makeDirectory($dir, 0755, true);
            }
        }
    }

    /**
     * @return void
     */
    protected function createExtensions(): void
    {
        $this->autoCreateDirs([
            base_path('extensions'),
        ]);
    }
}

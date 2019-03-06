<?php

namespace CrCms\Microservice\Console\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class ModuleMakeCommand
 * @package CrCms\Microservice\Console\Commands
 */
class ModuleMakeCommand extends InitializeMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module';

    /**
     * @return void
     */
    public function handle(): void
    {
        $name = Str::ucfirst($this->argument('name'));

        $this->createModules($name);
        $this->createRoutes($name);
        $this->createDatabase($name);
        $this->createConfigFile($name);

        $this->info("The module:{$name} create success");
    }

    /**
     * @param string $name
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @return void
     */
    protected function createModules(string $name): void
    {
        $this->autoCreateDirs([
            base_path('modules/'.$name.'/Schedules'),
            base_path('modules/'.$name.'/Config'),
            base_path('modules/'.$name.'/Commands'),
            base_path('modules/'.$name.'/Events'),
            base_path('modules/'.$name.'/Exceptions'),
            base_path('modules/'.$name.'/Handlers'),
            base_path('modules/'.$name.'/Tasks'),
            base_path('modules/'.$name.'/Jobs'),
            base_path('modules/'.$name.'/Listeners'),
            base_path('modules/'.$name.'/Models'),
            base_path('modules/'.$name.'/Providers'),
            base_path('modules/'.$name.'/Repositories/Constants'),
            base_path('modules/'.$name.'/Middleware'),
            base_path('modules/'.$name.'/DataProviders'),
            base_path('modules/'.$name.'/Controllers'),
            base_path('modules/'.$name.'/Resources'),
            base_path('modules/'.$name.'/Routes'),
            base_path('modules/'.$name.'/Translations'),
            base_path('modules/'.$name.'/Database/Factories'),
            base_path('modules/'.$name.'/Database/Migrations'),
            base_path('modules/'.$name.'/Database/Seeds'),
        ]);
    }

    /**
     * Create module config
     *
     * @param string $name
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createConfigFile(string $name): void
    {
        $file = base_path('modules/'.$name.'/Config/config.php');
        if (!$this->files->exists($file)) {
            $this->files->put($file, $this->files->get(__DIR__.'/stubs/config.stub'));
        }
    }

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

    /**
     * @param string $name
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return void
     */
    protected function createDatabase(string $name): void
    {
        $this->files->put('modules/'.$name.'/Database/Factories/UserFactory.php', $this->files->get(__DIR__ . '/stubs/factory.stub'));
        $this->files->put('modules/'.$name.'/Database/Migrations/2014_10_12_000000_create_users_table.php', $this->files->get(__DIR__ . '/stubs/migration.stub'));
        $this->files->put('modules/'.$name.'/Database/Seeds/DatabaseSeeder.php', $this->files->get(__DIR__ . '/stubs/seed.stub'));
    }

    /**
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The module name'],
        ];
    }
}
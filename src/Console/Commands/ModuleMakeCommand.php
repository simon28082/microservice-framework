<?php

namespace CrCms\Microservice\Console\Commands;

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
        $this->createModules($this->argument('name'));
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
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The module name.'],
        ];
    }
}
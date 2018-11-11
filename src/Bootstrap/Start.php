<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-10 19:21
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Bootstrap;

use CrCms\Microservice\Foundation\Application;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Start
 * @package CrCms\Microservice\Foundation
 */
class Start
{
    /**
     * @var Application
     */
    protected $app;

    public static function run(array $params = [], ?string $basePath = null)
    {
        $instance = new static;
        $instance->createApplication($basePath);
        $instance->baseKernelBinding();

        $instance->getApplication()->runningInConsole() ?
            $instance->runConsole($params) : $instance->runApplication($params);
    }

    public function createApplication(string $basePath)
    {
        $this->app = new Application($basePath);
        return $this;
    }

    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * @return void
     */
    protected function baseKernelBinding():  void
    {
        $this->app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \CrCms\Microservice\Console\Kernel::class
        );

        $this->app->singleton(
            \CrCms\Microservice\Console\Contracts\ExceptionHandlerContract::class,
            \CrCms\Microservice\Console\ExceptionHandler::class
        );
    }

    /**
     * @param array $params
     * @return void
     */
    protected function runApplication(array $params)
    {
        //$this->app->run();
        var_dump('run');
    }

    /**
     * @param array $params
     * @return void
     */
    protected function runConsole(array $params)
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);

        $status = $kernel->handle(
            $input = new ArgvInput(array_values($params)),
            new ConsoleOutput
        );

        $kernel->terminate($input, $status);

        exit($status);
    }
}
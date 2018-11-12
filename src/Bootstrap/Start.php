<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-10 19:21
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Bootstrap;

use CrCms\Microservice\Foundation\Application;
use CrCms\Microservice\Server\Factory;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use CrCms\Microservice\Console\Kernel;
use CrCms\Microservice\Console\Contracts\ExceptionHandlerContract as ConsoleExceptionHandlerContract;
use CrCms\Microservice\Server\Contracts\ExceptionHandlerContract as ServerExceptionHandlerContract;
use CrCms\Microservice\Console\ExceptionHandler as ConsoleExceptionHandler;
use CrCms\Microservice\Server\Contracts\KernelContract as ServerKernelContract;
use CrCms\Microservice\Server\Kernel as ServerKernel;

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

    /**
     * @var array
     */
    protected $servers = [
        'http' => [
            'exception' => \CrCms\Microservice\Server\Http\Exception\ExceptionHandler::class
        ]
    ];

    /**
     * @var string
     */
    protected $mode;

    /**
     * @param array $params
     * @param null|string $basePath
     * @param null|string $mode
     * @return void
     */
    public static function run(array $params = [], ?string $basePath = null, ?string $mode = null): void
    {
        $instance = new static;
        $instance->mode($mode);
        $instance->createApplication($basePath)->baseKernelBinding();

        $instance->getApplication()->runningInConsole() ?
            $instance->runConsole($params) : $instance->runApplication($params);
    }

    /**
     * @param string $basePath
     * @return Start
     */
    public function createApplication(string $basePath): self
    {
        $this->app = new Application($basePath);
        return $this;
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->app;
    }

    /**
     * @param null|string $mode
     * @return Start
     */
    protected function mode(?string $mode = null): self
    {
        $envMode = getenv('CRCMS_MODE');
        if ($envMode !== false) {
            $mode = $envMode;
        }
        $mode = strtolower($mode);
        if (!array_key_exists($mode, $this->servers)) {
            $mode = 'http';
        }

        putenv("CRCMS_MODE={$mode}");
        $this->mode = $mode;

        return $this;
    }

    /**
     * @param array $params
     * @return void
     */
    protected function runConsole(array $params): void
    {
        $kernel = $this->app->make(KernelContract::class);

        $status = $kernel->handle(
            $input = new ArgvInput(array_values($params)),
            new ConsoleOutput
        );

        $kernel->terminate($input, $status);

        exit($status);
    }

    /**
     * @return void
     */
    protected function baseKernelBinding(): void
    {
        $this->app->singleton(
            KernelContract::class,
            Kernel::class
        );

        $this->app->singleton(
            ConsoleExceptionHandlerContract::class,
            ConsoleExceptionHandler::class
        );

        $this->app->singleton(
            ServerKernelContract::class,
            ServerKernel::class
        );

        $this->app->singleton(
            ServerExceptionHandlerContract::class,
            $this->servers[$this->mode]['exception']
        );
    }

    /**
     * @param array $params
     * @return void
     */
    protected function runApplication(array $params): void
    {
        $service = Factory::service($this->app, $this->mode);

        $kernel = $this->app->make(ServerKernelContract::class);

        $response = $kernel->handle($service);

        $response->send();

        $kernel->terminate($service);
    }
}
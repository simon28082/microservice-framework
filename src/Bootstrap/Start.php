<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-10 19:21
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Bootstrap;

use CrCms\Microservice\Console\Kernel;
use CrCms\Microservice\Foundation\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use CrCms\Microservice\Foundation\Kernel as ServerKernel;
use CrCms\Microservice\Foundation\Exceptions\ExceptionHandler;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use CrCms\Microservice\Server\Contracts\KernelContract as ServerKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ServerExceptionHandlerContract;

class Start
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param array       $params
     * @param null|string $basePath
     *
     * @return void
     */
    public static function run(array $params = [], ?string $basePath = null): void
    {
        $instance = static::instance();

        $instance->bootstrap($basePath);

        $instance->runConsole($params);
    }

    /**
     * @return Start
     */
    public static function instance(): self
    {
        return new static();
    }

    /**
     * @param null|string $basePath
     * @param null|string $mode
     *
     * @return Start
     */
    public function bootstrap(?string $basePath = null): self
    {
        $this->createApplication($basePath);
        $this->baseKernelBinding();

        return $this;
    }

    /**
     * @param null|string $basePath
     *
     * @return Start
     */
    public function createApplication(?string $basePath = null): self
    {
        $basePath ?: $basePath = realpath(__DIR__.'/../../../../../');

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
     * @param array $params
     *
     * @return void
     */
    protected function runConsole(array $params): void
    {
        $kernel = $this->app->make(KernelContract::class);

        $status = $kernel->handle(
            $input = new ArgvInput(array_values($params)),
            new ConsoleOutput()
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
            ServerKernelContract::class,
            ServerKernel::class
        );

        $this->app->singleton(
            ServerExceptionHandlerContract::class,
            ExceptionHandler::class
        );
    }
}

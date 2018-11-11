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
use CrCms\Microservice\Console\Contracts\ExceptionHandlerContract;
use CrCms\Microservice\Console\ExceptionHandler;
use CrCms\Microservice\Server\Contracts\KernelContract as ServerKernelContract;

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
     * @param array $params
     * @param null|string $basePath
     */
    public static function run(array $params = [], ?string $basePath = null)
    {
        $instance = new static;
        $instance->createApplication($basePath)->baseKernelBinding();

        $instance->getApplication()->runningInConsole() ?
            $instance->runConsole($params) : $instance->runApplication($params);
    }

    /**
     * @param string $basePath
     * @return $this
     */
    public function createApplication(string $basePath)
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
     * @param array $params
     * @return void
     */
    protected function runConsole(array $params)
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
    protected function baseKernelBinding():  void
    {
        $this->app->singleton(
            KernelContract::class,
            Kernel::class
        );

        $this->app->singleton(
            ExceptionHandlerContract::class,
            ExceptionHandler::class
        );
    }

    /**
     * @param array $params
     * @return void
     */
    protected function runApplication(array $params)
    {
        $kernel = $this->app->make(ServerKernelContract::class);
//        $response = $kernel->handle(
//            $request = Illuminate\Http\Request::capture()
//        );
//        $response->send();
//        $kernel->terminate($request, $response);

        $kernel->bootstrap();

        $service = Factory::service($this,$this['config']->get('ms.default'));
        //这里还有问题，一旦被Service之前有异常或出错，则会报ExceptionHandlerContract没有绑定
//        $this->singleton(
//            ExceptionHandlerContract::class,
//            $service::exceptionHandler()
//        );

        $response = $kernel->handle(
            $service
        );

        $response->send();

        $kernel->terminate($service);

    }
}
<?php
/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2019-01-19 21:20
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2019 Rights Reserved CRCMS
 */

$app = new \CrCms\Microservice\Foundation\Application(__DIR__);
\CrCms\Microservice\Foundation\Application::setInstance($app);

function forkApp()
{
    return \CrCms\Microservice\Foundation\Application::getInstance();
}


$config = new \CrCms\Microservice\Bootstrap\LoadConfiguration();
$config->bootstrap($app);
$app->make('config')->set(['app.debug' => true]);
$app->make('config')->set(['app.key' => 'base64:Bey9po1NfR9CHY65KxPqQIemqvhDfHLNTFeffewn3pY=']);


$app->singleton(
    \CrCms\Microservice\Server\Contracts\KernelContract::class,
    \CrCms\Microservice\Foundation\Kernel::class
);

$app->singleton(
    \Illuminate\Contracts\Debug\ExceptionHandler::class,
    \CrCms\Microservice\Foundation\Exceptions\ExceptionHandler::class
);

//$providers = $app->make('config')->get('mount.providers');
//$providers[] = \CrCms\Microservice\Server\ServerServiceProvider::class;
//$app->make('config')->set(['mount.providers' => $providers]);
//dd($app->make('config')->get('mount.providers'));
$provider = new \CrCms\Microservice\Bootstrap\RegisterProviders();
$provider->bootstrap($app);

$boot = new \CrCms\Microservice\Bootstrap\BootProviders();
$boot->bootstrap($app);



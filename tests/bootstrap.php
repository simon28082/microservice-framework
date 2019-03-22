<?php
/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2019-01-19 21:20
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2019 Rights Reserved CRCMS
 */

$basePath = __DIR__.'/tmp';

if (!is_dir($basePath)) {
    mkdir($basePath, 0755, true);
}

$app = new \CrCms\Microservice\Foundation\Application(__DIR__);
\CrCms\Microservice\Foundation\Application::setInstance($app);


$app->singleton('config',function(){

    $configPath = __DIR__.'/../config/';
    $files = glob($configPath.'*.php');

    $array = [];
    foreach ($files as $file) {
        $array[basename($file)] = require $file;
    }

   return new \Illuminate\Config\Repository($array);
});

$app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class,\CrCms\Microservice\Foundation\Exceptions\ExceptionHandler::class);

/**
 * forkApp
 *
 * @return \CrCms\Microservice\Foundation\Application
 */
function forkApp(): \CrCms\Microservice\Foundation\Application
{
    return \CrCms\Microservice\Foundation\Application::getInstance();
}



<?php
/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-10 20:02
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

//namespace CrCms\Microservice\Foundation\Helpers;

use Illuminate\Container\Container;
use Illuminate\Support\Str;

function app($abstract = null, array $parameters = [])
{
    if (is_null($abstract)) {
        return Container::getInstance();
    }

    return Container::getInstance()->make($abstract, $parameters);
}

function storage_path(?string $path = null): string
{
    return app()->storagePath($path);
}

function database_path(?string $path = null): string
{
    return app()->databasePath($path);
}


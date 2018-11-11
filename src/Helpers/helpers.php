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

function base_path(?string $path = null): string
{
    $basePath = app()->basePath();
    return $path ? $basePath.DIRECTORY_SEPARATOR.$path : $basePath;
}

function database_path(?string $path = null): string
{
    return app()->databasePath($path);
}


/**
 * Translate the given message.
 *
 * @param  string|null  $id
 * @param  array   $replace
 * @param  string|null  $locale
 * @return \Illuminate\Contracts\Translation\Translator|string|array|null
 */
function trans($id = null, $replace = [], $locale = null)
{
    if (is_null($id)) {
        return app('translator');
    }

    return app('translator')->trans($id, $replace, $locale);
}

function config($key = null, $default = null)
{
    if (is_null($key)) {
        return app('config');
    }

    if (is_array($key)) {
        return app('config')->set($key);
    }

    return app('config')->get($key, $default);
}

function bcrypt($value, $options = [])
{
    return app('hash')->driver('bcrypt')->make($value, $options);
}

function cache()
{
    $arguments = func_get_args();

    if (empty($arguments)) {
        return app('cache');
    }

    if (is_string($arguments[0])) {
        return app('cache')->get(...$arguments);
    }

    if (! is_array($arguments[0])) {
        throw new Exception(
            'When setting a value in the cache, you must pass an array of key / value pairs.'
        );
    }

    if (! isset($arguments[1])) {
        throw new Exception(
            'You must specify an expiration time when setting a value in the cache.'
        );
    }

    return app('cache')->put(key($arguments[0]), reset($arguments[0]), $arguments[1]);
}

function encrypt($value, $serialize = true)
{
    return app('encrypter')->encrypt($value, $serialize);
}

function logger($message = null, array $context = [])
{
    if (is_null($message)) {
        return app('log');
    }

    return app('log')->debug($message, $context);
}

<?php
/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-10 20:02
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */
use Illuminate\Container\Container;
use CrCms\Microservice\Routing\ResponseResource;

/**
 * @param null  $abstract
 * @param array $parameters
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return Container|mixed
 */
function app($abstract = null, array $parameters = [])
{
    if (is_null($abstract)) {
        return Container::getInstance();
    }

    return Container::getInstance()->make($abstract, $parameters);
}

/**
 * @param null|string $path
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return string
 */
function storage_path(?string $path = null): string
{
    return app()->storagePath($path);
}

/**
 * @param null|string $path
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return string
 */
function base_path(?string $path = null): string
{
    $basePath = app()->basePath();

    return $path ? $basePath.DIRECTORY_SEPARATOR.$path : $basePath;
}

/**
 * @param null|string $path
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return string
 */
function database_path(?string $path = null): string
{
    return app()->databasePath($path);
}

/**
 * @param null|string $path
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return string
 */
function app_path(?string $path = null): string
{
    $appPath = base_path('app');

    return $path ? $appPath.DIRECTORY_SEPARATOR.$path : $appPath;
}

/**
 * @param null|string $path
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return string
 */
function config_path(?string $path = null): string
{
    return app()->configPath($path);
}

/**
 * @param null|string $path
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return string
 */
function resource_path(?string $path = null): string
{
    return app()->resourcePath($path);
}

/**
 * Translate the given message.
 *
 * @param string|null $id
 * @param array       $replace
 * @param string|null $locale
 *
 * @return \Illuminate\Contracts\Translation\Translator|string|array|null
 */
function trans($id = null, $replace = [], $locale = null)
{
    if (is_null($id)) {
        return app('translator');
    }

    return app('translator')->trans($id, $replace, $locale);
}

/**
 * @param null $key
 * @param null $default
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return Container|mixed
 */
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

/**
 * @param $value
 * @param array $options
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return mixed
 */
function bcrypt($value, $options = [])
{
    return app('hash')->driver('bcrypt')->make($value, $options);
}

/**
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return Container|mixed
 */
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

/**
 * @param $value
 * @param bool $serialize
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return mixed
 */
function encrypt($value, $serialize = true)
{
    return app('encrypter')->encrypt($value, $serialize);
}

/**
 * @param $value
 * @param bool $unserialize
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return mixed
 */
function decrypt($value, $unserialize = true)
{
    return app('encrypter')->decrypt($value, $unserialize);
}

/**
 * @param $job
 *
 * @return PendingDispatch
 */
function dispatch($job)
{
    if ($job instanceof Closure) {
        $job = new CallQueuedClosure(new SerializableClosure($job));
    }

    return new PendingDispatch($job);
}

/**
 * @param mixed ...$args
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return mixed
 */
function event(...$args)
{
    return app('events')->dispatch(...$args);
}

/**
 * @param null  $message
 * @param array $context
 *
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return Container|mixed
 */
function logger($message = null, array $context = [])
{
    if (is_null($message)) {
        return app('log');
    }

    return app('log')->debug($message, $context);
}

/**
 * @throws \Illuminate\Contracts\Container\BindingResolutionException
 *
 * @return ResponseResource
 */
function response(): ResponseResource
{
    return app(ResponseResource::class);
}

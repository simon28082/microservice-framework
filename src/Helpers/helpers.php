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

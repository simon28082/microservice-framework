<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-12 20:06
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Foundation;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\ServiceProvider;

/**
 * Class CrCmsServiceProvider
 * @package CrCms\Microservice\src\Foundation
 */
class CrCmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->afterResolving(ValidatesWhenResolved::class, function ($resolved) {
            $resolved->validateResolved();
        });
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return [
        ];
    }
}
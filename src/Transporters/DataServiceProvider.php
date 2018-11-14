<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-12 20:06
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Transporters;

use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\ServiceProvider;

/**
 * Class DataServiceProvider
 * @package CrCms\Microservice\Transporters
 */
class DataServiceProvider extends ServiceProvider
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

        $this->app->resolving(DataProvider::class, function (DataProvider $dataProvider, $app) {
            $dataProvider->setObject($app['service']->getDataProvider()->all())->setService($app['service']);
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
}
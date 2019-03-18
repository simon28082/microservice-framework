<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-12 20:06
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Foundation;

use CrCms\Microservice\Bridging\BridgingServiceProvider;
use Illuminate\Support\AggregateServiceProvider;
use CrCms\Microservice\Server\ServerServiceProvider;
use CrCms\Foundation\Transporters\DataServiceProvider;
use CrCms\Microservice\Dispatching\DispatchingServiceProvider;
use Illuminate\Translation\FileLoader;

/**
 * Class CrCmsServiceProvider.
 */
class CrCmsServiceProvider extends AggregateServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        DataServiceProvider::class,
        ServerServiceProvider::class,
        DispatchingServiceProvider::class,
        BridgingServiceProvider::class,
    ];

    /**
     * Register
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->singleton('translation.loader', function ($app) {
            return new FileLoader($app['files'], realpath(__DIR__.'/../../resources/lang'));
        });
    }

    /**
     * Provides
     *
     * @return array
     */
    public function provides()
    {
        $provides = parent::provides();

        return array_merge($provides,['translation.loader']);
    }
}

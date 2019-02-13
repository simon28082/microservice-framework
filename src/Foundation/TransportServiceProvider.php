<?php

namespace CrCms\Microservice\Foundation;

use CrCms\Microservice\Bridging\DataPacker;
use CrCms\Microservice\Bridging\Packer\JsonPacker;
use Illuminate\Support\ServiceProvider;

class TransportServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        $this->registerAlias();

        $this->registerServices();
    }

    /**
     * registerAlias
     *
     * @return void
     */
    protected function registerAlias(): void
    {
        $this->app->alias('transport.packer', DataPacker::class);
    }

    /**
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->singleton('transport.packer', function ($app) {
            $encryption = $app['config']->get('app.encryption');
            return new DataPacker(new JsonPacker, $encryption === true ? $app['encrypter'] : null);
        });
    }

    /**
     * provides
     *
     * @return array
     */
    public function provides(): array
    {
        return ['transport.packer'];
    }
}

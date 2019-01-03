<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-14 23:17
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Server;

use CrCms\Microservice\Routing\Route;
use CrCms\Microservice\Server\Contracts\ResponseContract;
use CrCms\Microservice\Server\Events\RequestHandling;
use CrCms\Microservice\Server\Http\Request;
use CrCms\Microservice\Server\Http\Response;
use CrCms\Microservice\Server\Packer\Contracts\SecretContract;
use CrCms\Microservice\Server\Packer\Packer;
use CrCms\Microservice\Server\Packer\Secret;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

/**
 * Class ServerServiceProvider
 * @package CrCms\Microservice\Server
 */
class ServerServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->app['events']->listen(RequestHandling::class, function (RequestHandling $event) {
            if ($event->request instanceof Request && $event->request->method() !== 'POST') {
                return $this->allServices();
            }
        });

        //merge server config to swoole config
        $this->mergeServerConfigToSwoole();
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->registerAlias();

        $this->registerServices();
    }

    /**
     * @return void
     */
    protected function registerAlias(): void
    {
        $this->app->alias('server.secret', SecretContract::class);
        $this->app->alias('server.packer', Packer::class);
    }

    /**
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->singleton('server.secret', function ($app) {
            $config = $app->make('config')->get('app');
            return new Secret($config['secret'], $config['secret_cipher']);
        });

        $this->app->singleton('server.packer', function ($app) {
            return new Packer($app['server.secret']);
        });
    }

    /**
     * @return ResponseContract
     */
    protected function allServices(): ResponseContract
    {
        $methods = (new Collection($this->app->make('router')->getRoutes()->get()))->mapWithKeys(function (Route $route) {
            $uses = $route->getAction('uses');
            $uses = $uses instanceof \Closure ? 'Closure' : $uses;
            return [$route->mark() => $uses];
        })->toArray();

        return new Response(['methods' => $methods], 200);
    }

    /**
     * @return void
     */
    protected function mergeServerConfigToSwoole(): void
    {
        $server = $this->app['config']->get('server', []);
        $swoole = $this->app['config']->get('swoole', []);
        $this->app['config']->set(['swoole' => array_merge($swoole, $server)]);
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return [
            'server.packer',
            'server.secret',
        ];
    }
}
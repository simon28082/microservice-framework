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
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

/**
 * Class ServerServiceProvider
 * @package CrCms\Microservice\Server
 */
class ServerServiceProvider extends ServiceProvider
{
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
}
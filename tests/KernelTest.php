<?php

namespace CrCms\Microservice\Tests;

use CrCms\Microservice\Dispatching\Dispatcher;
use CrCms\Microservice\Foundation\Kernel;
use CrCms\Microservice\Server\Http\Request;
use PHPUnit\Framework\TestCase;

class KernelTest extends TestCase
{
    use ApplicationTrait;

    public function testKernel()
    {
//        var_dump($_ENV);
        $kernel = new Kernel(static::$app,new Dispatcher(static::$app));
        $kernel->bootstrap();

        $request = $kernel->handle(new Request(static::$app,\Illuminate\Http\Request::capture()));
        $content = static::$app->make('server.packer')->pack(['call'=>'test','data'=>['x'=>1]]);
//        $response = $kernel->transport($content);
        dd($content);
        dd($request);
    }

}

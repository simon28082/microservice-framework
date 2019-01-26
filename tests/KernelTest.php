<?php

namespace CrCms\Microservice\Tests;

use CrCms\Microservice\Foundation\Kernel;
use CrCms\Microservice\Server\Http\Request;
use PHPUnit\Framework\TestCase;

class KernelTest extends TestCase
{
    use ApplicationTrait;

    public function testKernel()
    {
        $kernel = new Kernel(static::$app);

        $request = $kernel->request(new Request(static::$app,\Illuminate\Http\Request::capture()));
        $content = static::$app->make('server.packer')->pack(['call'=>'test','data'=>['x'=>1]]);
        $response = $kernel->transport($content);
        dd($response);
        dd($request);
    }

}

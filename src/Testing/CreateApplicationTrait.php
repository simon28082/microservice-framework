<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-18 15:16
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Testing;

use CrCms\Microservice\Bootstrap\Start;
use CrCms\Microservice\Foundation\Application;
use Illuminate\Contracts\Console\Kernel as KernelContract;

/**
 * Trait CreateApplicationTrait.
 */
trait CreateApplicationTrait
{
    /**
     * @return Application
     */
    public function createApplication(): Application
    {
        return tap(Start::instance()->bootstrap()->getApplication(), function (Application $app) {
            $app->make(KernelContract::class)->bootstrap();
        });
    }
}

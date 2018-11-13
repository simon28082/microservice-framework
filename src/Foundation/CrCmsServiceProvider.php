<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-12 20:06
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Foundation;

use CrCms\Microservice\Transporters\DataServiceProvider;
use Illuminate\Support\AggregateServiceProvider;

/**
 * Class CrCmsServiceProvider
 * @package CrCms\Microservice\src\Foundation
 */
class CrCmsServiceProvider extends AggregateServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        DataServiceProvider::class,
    ];
}
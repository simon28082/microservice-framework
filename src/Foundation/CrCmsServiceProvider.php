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

use Illuminate\Support\AggregateServiceProvider;
use CrCms\Microservice\Server\ServerServiceProvider;
use CrCms\Foundation\Transporters\DataServiceProvider;

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
    ];
}

<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-08-12 11:00
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Transporters;

use CrCms\Foundation\Transporters\AbstractDataProvider;
use CrCms\Microservice\Server\Contracts\ServiceContract;
use CrCms\Microservice\Transporters\Concerns\ValidateConcern;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;

/**
 * Class DataProvider
 * @package CrCms\Microservice\Transporters
 */
class DataProvider extends AbstractDataProvider implements ValidatesWhenResolved
{
    use ValidateConcern;

    /**
     * @var
     */
    protected $service;

    /**
     * @param ServiceContract $service
     * @return DataProvider
     */
    public function setService(ServiceContract $service): self
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return ServiceContract
     */
    public function getService(): ServiceContract
    {
        return $this->service;
    }
}
<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2019-03-21 07:34
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2019 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Tests\Exceptions;


class ResourceException extends \Exception
{

    public function getStatusCode()
    {
        return 403;
    }

}
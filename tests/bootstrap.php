<?php
/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2019-01-19 21:20
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2019 Rights Reserved CRCMS
 */

function forkApp($path = __DIR__)
{
    return new \CrCms\Microservice\Foundation\Application($path);
}
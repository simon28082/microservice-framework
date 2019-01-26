<?php
/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2019-01-19 21:20
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2019 Rights Reserved CRCMS
 */

$app = new \CrCms\Microservice\Foundation\Application(__DIR__);
\CrCms\Microservice\Foundation\Application::setInstance($app);

function forkApp()
{
    return \CrCms\Microservice\Foundation\Application::getInstance();
}

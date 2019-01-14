<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-09 19:47
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Server\Contracts;

/**
 * Interface ResponseContract.
 */
interface ResponseContract
{
    /**
     * @return mixed
     */
    public function send();

    /**
     * @param $response
     *
     * @return ResponseContract
     */
    public static function createResponse($response): self;

    /**
     * @return mixed
     */
    public function getContent();
}

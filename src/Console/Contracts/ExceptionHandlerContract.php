<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-11 10:13
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Console\Contracts;

use Exception;

/**
 * Class ExceptionHandlerContract
 * @package CrCms\Microservice\Console\Contracts
 */
interface ExceptionHandlerContract
{
    /**
     * Report or log an exception.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e);

    /**
     * Render an exception to the console.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  \Exception  $e
     * @return void
     */
    public function render($output, Exception $e);
}
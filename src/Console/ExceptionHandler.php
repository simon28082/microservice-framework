<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-11 10:15
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Console;

use CrCms\Microservice\Console\Contracts\ExceptionHandlerContract;
use Exception;
use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class ExceptionHandler
 * @package CrCms\Microservice\Console\Commands
 */
class ExceptionHandler implements ExceptionHandlerContract
{
    /**
     * The container implementation.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * ExceptionHandler constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Exception $e
     * @throws Exception
     */
    public function report(Exception $e)
    {
        if (method_exists($e, 'report')) {
            return $e->report();
        }

        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $e;
        }

        $logger->error(
            $e->getMessage(),
            ['exception' => $e]);
    }

    /**
     * @param Exception $e
     */
    public function render(Exception $e)
    {
        (new ConsoleApplication)->renderException($e, new ConsoleOutput);
    }
}
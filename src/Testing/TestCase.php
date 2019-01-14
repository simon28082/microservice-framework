<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-18 11:35
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Testing;

use CrCms\Microservice\Foundation\Application;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Class TestCase.
 */
abstract class TestCase extends BaseTestCase
{
    use CreateApplicationTrait;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->app = $this->createApplication();
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        $this->app->flush();
    }
}

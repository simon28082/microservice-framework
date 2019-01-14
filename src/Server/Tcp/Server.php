<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/6/16 17:41
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Server\Tcp;

use Swoole\Server as SwooleServer;
use CrCms\Microservice\Server\Kernel;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\ServerContract;
use CrCms\Microservice\Server\Tcp\Events\ReceiveEvent;

/**
 * Class Server.
 */
class Server extends AbstractServer implements ServerContract
{
    /**
     * @var array
     */
    protected $events = [
        'receive' => ReceiveEvent::class,
    ];

    /**
     * @return void
     */
    public function bootstrap(): void
    {
        //$this->app->make(Kernel::class)->bootstrap();
        $this->app->instance('server', $this);
    }

    /**
     * @param array $config
     *
     * @return void
     */
    public function createServer(): void
    {
        $serverParams = [
            $this->config['host'],
            $this->config['port'],
            $this->config['mode'] ?? SWOOLE_PROCESS,
            $this->config['type'] ?? SWOOLE_SOCK_TCP,
        ];

        $this->server = new SwooleServer(...$serverParams);
        $this->setPidFile();
        $this->setSettings($this->config['settings'] ?? []);
        $this->eventDispatcher($this->config['events'] ?? []);
    }

    /**
     * @return void
     */
    protected function setPidFile()
    {
        if (empty($this->config['settings']['pid_file'])) {
            $this->config['settings']['pid_file'] = $this->pidFile();
        }
    }
}

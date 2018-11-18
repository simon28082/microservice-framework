<?php

namespace CrCms\Microservice\Console\Commands;

use CrCms\Microservice\Server\Http\Server;
use CrCms\Server\AbstractServerCommand;
use CrCms\Server\Server\Contracts\ServerContract;
use Illuminate\Filesystem\Filesystem;

/**
 * Class SwooleServerCommand
 * @package CrCms\Foundation\Http\Commands
 */
class SwooleServerCommand extends AbstractServerCommand
{
    /**
     * @var string
     */
    protected $signature = 'server {server : http or tcp} {action : start or stop or restart}';
//?=http
    /**
     * @var string
     */
    protected $description = 'Swoole server';

    /**
     * @return ServerContract
     */
    public function server(): ServerContract
    {
        $serverType = $this->argument('server');
        $server = $this->getServer($serverType);

        $this->cleanRunCache();

        return new $server(
            $this->getLaravel(),
            config("swoole.servers.{$serverType}"),
            'microservice.'.$serverType
        );
    }

    /**
     * @param string $server
     * @return string
     */
    protected function getServer(string $server): string
    {
        switch ($server) {
            case 'http':
                return Server::class;
                break;
            case 'tcp':
                return \CrCms\Microservice\Server\Tcp\Server::class;
                break;
        }
    }

    /**
     * @return void
     */
    protected function cleanRunCache(): void
    {
        (new Filesystem())->cleanDirectory(
            dirname($this->getLaravel()->getCachedServicesPath())
        );
    }
}
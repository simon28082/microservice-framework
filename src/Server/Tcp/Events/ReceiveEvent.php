<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-18 17:22
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Server\Tcp\Events;

use CrCms\Microservice\Server\Kernel;
use CrCms\Server\Server\AbstractServer;
use CrCms\Microservice\Server\Tcp\Request;
use CrCms\Server\Server\Events\AbstractEvent;

/**
 * Class ReceiveEvent.
 */
class ReceiveEvent extends AbstractEvent
{
    /**
     * @var int
     */
    protected $fd;

    /**
     * @var int
     */
    protected $formId;

    /**
     * @var string
     */
    protected $data;

    /**
     * ReceiveEvent constructor.
     *
     * @param int    $fd
     * @param int    $fromId
     * @param string $data
     */
    public function __construct(int $fd, int $fromId, string $data)
    {
        $this->fd = $fd;
        $this->formId = $fromId;
        $this->data = $data;
//        dump($this->data);
        //logger($this->data);
    }

    /**
     * @param AbstractServer $server
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        $data = explode('\\r\\n', $this->data);
        foreach ($data as $value) {
        }

        $kernel = $server->getApplication()->make(Kernel::class);
    }

    /**
     * @return void
     */
    protected function setResponse(Kernel $kernel)
    {
        $request = $this->createRequest();

        $this->server->send('abc');
//        $response = $kernel->handle($request);
//
//        $this->response->end($response->getContent());
//
//        $kernel->terminate($request, $response);

        //$this->requestLog();
    }

    protected function createRequest()
    {
        return new Request($this->getServer()->getApplication(), $this->data);
    }
}

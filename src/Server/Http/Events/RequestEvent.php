<?php

namespace CrCms\Microservice\Server\Http\Events;

use Swoole\Http\Request;
use CrCms\Microservice\Server\Kernel;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Http\Request as ServerRequest;
use CrCms\Server\Server\Contracts\EventContract;
use CrCms\Server\Http\Response as ServerResponse;
use CrCms\Server\Http\Events\RequestEvent as BaseRequestEvent;
use CrCms\Microservice\Server\Http\Request as MicroserviceRequest;

/**
 * Class RequestEvent.
 */
class RequestEvent extends BaseRequestEvent implements EventContract
{
    /**
     * @param AbstractServer $server
     *
     * @return void
     */
    public function handle(AbstractServer $server): void
    {
        $this->server = $server;

        $app = $server->getApplication();

        $kernel = $app->make(Kernel::class);

        $microserviceRequest = new MicroserviceRequest(
            $app,
            ServerRequest::make($this->swooleRequest)->getIlluminateRequest()
        );

        $microserviceResponse = $kernel->handle($microserviceRequest);

        ServerResponse::make($this->swooleResponse, $microserviceResponse)->toResponse();

        $kernel->terminate($microserviceRequest, $microserviceResponse);
    }

    /**
     * @return Request
     */
    public function getSwooleRequest(): Request
    {
        return $this->swooleRequest;
    }
}

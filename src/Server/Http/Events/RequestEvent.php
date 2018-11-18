<?php

namespace CrCms\Microservice\Server\Http\Events;

use Carbon\Carbon;
use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Kernel;
use CrCms\Server\Server\AbstractServer;
use CrCms\Server\Server\Contracts\EventContract;
use CrCms\Server\Server\Events\AbstractEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class RequestEvent
 * @package CrCms\Microservice\Server\Http\Events
 */
class RequestEvent extends AbstractEvent implements EventContract
{
    /**
     * @var SwooleRequest
     */
    protected $request;

    /**
     * @var SwooleResponse
     */
    protected $response;

    /**
     * @var IlluminateRequest
     */
    protected $illuminateRequest;

    /**
     * @var IlluminateResponse
     */
    protected $illuminateResponse;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * Request constructor.
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     */
    public function __construct(SwooleRequest $request, SwooleResponse $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return void
     */
    public function handle(AbstractServer $server): void
    {
        parent::handle($server);

        $kernel = $server->getApplication()->make(Kernel::class);

        $this->setResponse($kernel);
    }

    /**
     * @return SwooleRequest
     */
    public function getRequest(): SwooleRequest
    {
        return $this->request;
    }

    /**
     * @return void
     */
    protected function setResponse(Kernel $kernel)
    {
        $request = $this->createRequest();

        $response = $kernel->handle($request);

        $this->response->end($response->getContent());

        $kernel->terminate($request, $response);

        //$this->requestLog();
    }

    /**
     * @return array
     */
    protected function mergePostData(): array
    {
        $data = [];

        if (strtoupper($this->request->server['request_method']) === 'POST') {
            $data = empty($this->request->post) ? [] : $this->request->post;

            if (isset($this->request->header['content-type']) && stripos($this->request->header['content-type'], 'application/json') !== false) {
                $data = array_merge($data, json_decode($this->request->rawContent(), true));
            }
        }

        return $data;
    }

    /**
     * @return RequestContract
     */
    protected function createRequest(): RequestContract
    {
        $request = new Request(
            $this->request->get ?? [],
            $this->mergePostData(),
            [],
            $this->request->cookie ?? [],
            $this->request->files ?? [],
            $this->mergeServerInfo()
            , $this->request->rawContent()
        );

        if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new ParameterBag($data);
        }

        return new \CrCms\Microservice\Server\Http\Request($this->getServer()->getApplication(), $request);
    }

    /**
     * @return array
     */
    protected function mergeServerInfo(): array
    {
        $server = $_SERVER;
        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
                $server['CONTENT_LENGTH'] = $_SERVER['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)) {
                $server['CONTENT_TYPE'] = $_SERVER['HTTP_CONTENT_TYPE'];
            }
        }

        $requestHeader = collect($this->request->header)->mapWithKeys(function ($item, $key) {
            $key = str_replace('-', '_', $key);
            return in_array(strtolower($key), ['x_real_ip'], true) ?
                [$key => $item] :
                ['http_' . $key => $item];
        })->toArray();

        $server = array_merge($server, $this->request->server, $requestHeader);

        return array_change_key_case($server, CASE_UPPER);
    }

    /**
     *
     */
    protected function requestLog(Request $illuminateRequest)
    {
        $params = http_build_query($illuminateRequest->all());
        $currentTime = Carbon::now()->toDateTimeString();
        $header = http_build_query($illuminateRequest->headers->all());

        $requestTime = Carbon::createFromTimestamp($illuminateRequest->server('REQUEST_TIME'));
        $content = "RecordTime:{$currentTime} RequestTime:{$requestTime} METHOD:{$illuminateRequest->method()} IP:{$illuminateRequest->ip()} Params:{$params} Header:{$header}" . PHP_EOL;

        $this->server->getProcess()->write($content);
    }
}
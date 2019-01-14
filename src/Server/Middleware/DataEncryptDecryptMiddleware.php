<?php

namespace CrCms\Microservice\Server\Middleware;

use Closure;
use UnexpectedValueException;
use CrCms\Microservice\Server\Packer\Packer;
use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Contracts\ResponseContract;

/**
 * Class DataEncryptDecryptMiddleware.
 */
class DataEncryptDecryptMiddleware
{
    /**
     * @var Packer
     */
    protected $packer;

    /**
     * DataEncryptDecrypt constructor.
     *
     * @param Packer $packer
     */
    public function __construct(Packer $packer)
    {
        $this->packer = $packer;
    }

    /**
     * @param RequestContract $request
     * @param Closure         $next
     *
     * @return mixed
     */
    public function handle(RequestContract $request, Closure $next)
    {
        /* 前置执行 */
        $data = $this->packer->unpack($request->rawData());

        $request->setCurrentCall($data['call']);
        $request->setData($data['data'] ?? []);

        /* @var ResponseContract $response */
        $response = $next($request);

        /* 后置执行 */
        $responseData = $response->getData(true);
        if (! empty($responseData)) {
            $response->setData($this->packer->pack($responseData));
        }

        return $response;
    }

    /**
     * @param $content
     *
     * @return array
     */
    protected function parseContent($content): array
    {
        $data = json_decode($content, true);
        if (json_last_error() !== 0) {
            throw new UnexpectedValueException('Parse data error: '.json_last_error_msg());
        }

        return $data;
    }
}

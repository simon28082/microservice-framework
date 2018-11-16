<?php

namespace CrCms\Microservice\Server\Middleware;

use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Contracts\ResponseContract;
use Closure;
use InvalidArgumentException;
use RangeException;

/**
 * Class DecryptData
 * @package CrCms\Microservice\Server\Middleware
 */
class DecryptData
{
    /**
     * @param RequestContract $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(RequestContract $request, Closure $next)
    {
        $request->setData($this->resolveData($request));

        return $next($request);
    }

    /**
     * @param RequestContract $request
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function resolveData(RequestContract $request): array
    {
        $rawData = $request->rawData();
        if (is_null($rawData)) {
            return [];
        }

        $parsedData = json_decode($rawData, true);
        if (json_last_error() !== 0) {
            throw new InvalidArgumentException("The raw data error");
        }

        if (config('app.secret_status') === false) {
            return $parsedData['data'];
        }

        $array = unserialize(
            openssl_decrypt(
                $parsedData['data'],
                config('app.secret_cipher'),
                config('app.secret'),
                OPENSSL_ZERO_PADDING,
                base64_decode($parsedData['iv'])
            )
        );

        if (!is_array($array)) {
            throw new RangeException("Parse content error : {$x}");
        }

        return $array;
    }
}
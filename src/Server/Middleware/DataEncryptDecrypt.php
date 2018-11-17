<?php

namespace CrCms\Microservice\Server\Middleware;

use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Contracts\ResponseContract;
use Closure;
use UnexpectedValueException;

/**
 * Class DataEncryptDecrypt
 * @package CrCms\Microservice\Server\Middleware
 */
class DataEncryptDecrypt
{
    /**
     * @param RequestContract $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(RequestContract $request, Closure $next)
    {
        $parseData = $this->parseData($request->rawData());

        /* 前置执行 */
        if (config('app.secret_status') === false) {
            $request->setData($parseData['data'] ?? []);
        } else {
            $request->setData($this->decrypt($parseData));
        }

        /* @var ResponseContract $response */
        $response = $next($request);

        /* 后置执行 */
        if (config('app.secret_status') === false) {
            return $response;
        } else {
            $data = $response->getData(true);
            if (!empty($data)) {
                $data = $this->encrypt($data);
                $response->setData($data);
            }
        }

        return $response;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function encrypt(array $data): array
    {
        $secretCipher = config('app.secret_cipher');
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($secretCipher));
        $data = openssl_encrypt(
            serialize($data),
            $secretCipher,
            config('app.secret'),
            OPENSSL_ZERO_PADDING,
            $iv
        );

        return ['data' => $data, 'iv' => base64_encode($iv)];
    }

    /**
     * @param $rawData
     * @return array
     */
    protected function parseData($rawData): array
    {
        if (empty($rawData)) {
            return [];
        }

        $parsedData = json_decode($rawData, true);
        if (json_last_error() !== 0) {
            throw new UnexpectedValueException("The raw data error");
        }

        return $parsedData;
    }

    /**
     * @param $parsedData
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function decrypt($parsedData): array
    {
        if (!isset($parsedData['data'])) {
            return [];
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
            throw new UnexpectedValueException("Parse content error : {$rawData}");
        }

        return $array;
    }
}
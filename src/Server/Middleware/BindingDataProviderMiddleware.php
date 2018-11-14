<?php

namespace CrCms\Microservice\Server\Middleware;

use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Exceptions\AccessDeniedException;
use Closure;

/**
 * Class BindingDataProviderMiddleware
 * @package CrCms\Microservice\Server\Middleware
 */
class BindingDataProviderMiddleware
{
    /**
     * @param RequestContract $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(RequestContract $request, Closure $next)
    {
        // 数据解密或校验
        $data = $request->all();
        if (false) {
            throw new AccessDeniedException("Illegal data");
        }

        return $next($request);
    }
}
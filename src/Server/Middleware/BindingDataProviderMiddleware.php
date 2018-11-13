<?php

namespace CrCms\Microservice\Server\Middleware;

use CrCms\Microservice\Server\Contracts\ServiceContract;
use CrCms\Microservice\Server\Exceptions\AccessDeniedException;
use CrCms\Microservice\Transporters\DataProvider;
use Closure;

/**
 * Class BindingDataProviderMiddleware
 * @package CrCms\Microservice\Server\Middleware
 */
class BindingDataProviderMiddleware
{
    /**
     * @param ServiceContract $service
     * @param Closure $next
     * @return mixed
     */
    public function handle(ServiceContract $service, Closure $next)
    {
        // 数据解密或校验
        $data = $service->getRequest()->data();
        if (false) {
            throw new AccessDeniedException("Illegal data");
        }

        // 存储数据
        $service->setDataProvider(new DataProvider($data));

        return $next($service);
    }
}
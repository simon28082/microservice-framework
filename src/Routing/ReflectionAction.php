<?php

namespace CrCms\Microservice\Routing;

use ReflectionClass;
use ReflectionMethod;

/**
 * Class ReflectionAction
 * @package CrCms\Microservice\Routing
 */
class ReflectionAction
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * ReflectionAction constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param string $controller
     * @return array
     * @throws \ReflectionException
     */
    public function getMethods(string $controller): array
    {
        $class = new ReflectionClass($controller);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        return array_filter(array_map(function (ReflectionMethod $method) {
            return $method->getName();
        }, $methods), function ($value) {
            return strpos($value, '__') !== 0;
        });
    }
}
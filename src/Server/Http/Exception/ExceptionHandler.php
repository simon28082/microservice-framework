<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-09 20:04
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Server\Http\Exception;

use CrCms\Microservice\Server\Contracts\ServiceContract;
use CrCms\Microservice\Server\Http\Response;
use Illuminate\Contracts\Container\Container;
use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use CrCms\Microservice\Server\Contracts\ExceptionHandlerContract;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ExceptionHandler
 * @package CrCms\Foundation\MicroService
 */
class ExceptionHandler implements ExceptionHandlerContract
{
    /**
     * The container implementation.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * A list of the internal exception types that should not be reported.
     *
     * @var array
     */
    protected $internalDontReport = [
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * ExceptionHandler constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Exception $e
     * @return mixed|void
     * @throws Exception
     */
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        if (method_exists($e, 'report')) {
            return $e->report();
        }

        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $e;
        }

        $logger->error(
            $e->getMessage(),
            ['exception' => $e]);
    }

    /**
     * @param Exception $e
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        return !$this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param  \Exception $e
     * @return bool
     */
    protected function shouldntReport(Exception $e)
    {
        $dontReport = array_merge($this->dontReport, $this->internalDontReport);

        return !is_null(Arr::first($dontReport, function ($type) use ($e) {
            return $e instanceof $type;
        }));
    }

    /**
     * @param ServiceContract $service
     * @param Exception $e
     * @return Response|\Illuminate\Http\Response|mixed|null|\Symfony\Component\HttpFoundation\Response
     */
    public function render(ServiceContract $service, Exception $e)
    {
        if (method_exists($e, 'render') && $response = $e->render($service)) {
            return new Response($response);
        } elseif ($e instanceof Responsable) {
            return $e->toResponse($service->getRequest());
        }

        $e = $this->prepareException($e);

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } elseif ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $service);
        }

        return $this->prepareJsonResponse($service, $e);
    }

    /**
     * @param Exception $e
     * @return bool
     */
    protected function isHttpException(Exception $e): bool
    {
        return $e instanceof HttpExceptionInterface;
    }

    /**
     * @param Exception $e
     * @return array
     */
    protected function convertExceptionToArray(Exception $e)
    {
        return config('app.debug') ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }

    /**
     * @param ServiceContract $service
     * @param Exception $e
     * @return Response
     */
    protected function prepareJsonResponse(ServiceContract $service, Exception $e)
    {
        return new Response(
            $this->convertExceptionToArray($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @param ValidationException $e
     * @param ServiceContract $service
     * @return Response|null|\Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, ServiceContract $service)
    {
        if ($e->response) {
            return $e->response;
        }

        return new Response([
            'message' => $e->getMessage(),
            'errors' => $e->errors(),
        ], $e->status);
    }

    /**
     * @param Exception $e
     * @return Exception|NotFoundHttpException
     */
    protected function prepareException(Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        return $e;
    }
}
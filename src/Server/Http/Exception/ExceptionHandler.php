<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-09 20:04
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\MicroService\Http;

//use CrCms\Foundation\MicroService\Contracts\ExceptionHandlerContract;
//use CrCms\Foundation\MicroService\Contracts\ServiceContract;
//use CrCms\Foundation\MicroService\Exceptions\ExceptionHandler as BaseExceptionHandler;
//use Illuminate\Contracts\Support\Responsable;
//use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Illuminate\Http\Exceptions\HttpResponseException;
//use Illuminate\Validation\ValidationException;
//use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
//use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
//use Exception as BaseException;
//use Illuminate\Support\Arr;
//use CrCms\Microservice\Microservice\Contracts\ExceptionHandlerContract;
//use CrCms\Microservice\Microservice\Contracts\ServiceContract;
use CrCms\Microservice\Server\Contracts\ServiceContract;
use Illuminate\Contracts\Container\Container;
use Exception;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use CrCms\Microservice\Server\Contracts\ExceptionHandlerContract;

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
//        HttpException::class,
//        HttpResponseException::class,
//        ModelNotFoundException::class,
//        TokenMismatchException::class,
//        ValidationException::class,
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
     * Create a new exception handler instance.
     *
     * @param  \Illuminate\Contracts\Container\Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Report or log an exception.
     *
     * @param  \Exception $e
     * @return mixed
     *
     * @throws \Exception
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
     * Determine if the exception should be reported.
     *
     * @param  \Exception $e
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
     * Determine if the given exception is an HTTP exception.
     *
     * @param BaseException $e
     * @return bool
     */
    protected function isHttpException(BaseException $e): bool
    {
        return $e instanceof HttpExceptionInterface;
    }

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
     * Prepare a JSON response for the given exception.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function prepareJsonResponse(ServiceContract $service, \Throwable $e)
    {
        return new Response(
            $this->convertExceptionToArray($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param ValidationException $e
     * @param ServiceContract $service
     * @return Response|null|\Symfony\Component\HttpFoundation\Response\
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, ServiceContract $service)
    {
        if ($e->response) {
            return $e->response;
        }

        return new Response([
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Prepare exception for rendering.
     *
     * @param  \Exception $e
     * @return \Exception
     */
    protected function prepareException(\Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        return $e;
    }
}
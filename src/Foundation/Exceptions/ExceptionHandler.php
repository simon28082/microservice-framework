<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-09 20:04
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Foundation\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use CrCms\Microservice\Server\Http\Response;
use Illuminate\Contracts\Container\Container;
use Illuminate\Validation\ValidationException;
use CrCms\Microservice\Server\Contracts\RequestContract;
use Symfony\Component\Console\Application as ConsoleApplication;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

/**
 * Class ExceptionHandler.
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
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Exception $e
     *
     * @throws Exception
     *
     * @return mixed|void
     */
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        if (is_callable($reportCallable = [$e, 'report'])) {
            return $this->container->call($reportCallable);
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
     *
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        return !$this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param \Exception $e
     *
     * @return bool
     */
    protected function shouldntReport(Exception $e)
    {
        $dontReport = array_merge($this->dontReport, $this->internalDontReport, $this->container['config']->get('exception.dont_report', []));

        return !is_null(Arr::first($dontReport, function ($type) use ($e) {
            return $e instanceof $type;
        }));
    }

    /**
     * @param RequestContract $request
     * @param Exception $e
     *
     * @return JsonResponse|array
     */
    public function render($request, Exception $e)
    {
        if ($this->isServiceException($e)) {
            $e->setRequest($request);
        } elseif ($e instanceof ValidationException) {
            $e = $this->convertValidationExceptionToEntityException($e);
        } else {
            $e = $this->convertExceptionToServiceException($e);
        }

        return $this->prepareJsonResponse($e);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param Exception $e
     */
    public function renderForConsole($output, Exception $e)
    {
        (new ConsoleApplication())->renderException($e, $output);
    }

    /**
     * Convert other exception to service exception
     *
     * @param Exception $e
     * @return ServiceException
     */
    protected function convertExceptionToServiceException(Exception $e): ServiceException
    {
        $exception = get_class($e);
        $conversion = $this->container['config']->get('exception.conversion', []);

        if (isset($conversion[$exception])) {
            return new $conversion[$exception]($e->getMessage(), $e->getCode(), $e);
        } /*else if (in_array($exception, $conversion, true)) {
            return new ServiceException($e->getMessage(), 400, $e);
        }*/ else {
            $statusCode = method_exists($e,'getStatusCode') ? $e->getStatusCode() : 400;
            return new ServiceException($e->getMessage(), $statusCode, $e);
        }
    }

    /**
     * @param Exception $e
     *
     * @return bool
     */
    protected function isServiceException(Exception $e): bool
    {
        return $e instanceof ServiceException;
    }

    /**
     * @param Exception $e
     *
     * @return array
     */
    protected function convertExceptionToArray(Exception $e)
    {
        return $this->container->make('config')->get('app.debug') ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'message' => $this->isServiceException($e) ? $e->getMessage() : 'Server Error',
        ];
    }

    /**
     * @param Exception $e
     *
     * @return JsonResponse
     */
    protected function prepareJsonResponse(Exception $e)
    {
        return new JsonResponse(
            $this->convertExceptionToArray($e),
            ($e->getCode() <= 0 || !is_numeric($e->getCode())) ? 500 : $e->getCode()
        );
    }

    /**
     * convertValidationExceptionToEntityException
     *
     * @param ValidationException $e
     * @return UnprocessableEntityException
     */
    protected function convertValidationExceptionToEntityException(ValidationException $e): UnprocessableEntityException
    {
        return new UnprocessableEntityException(Arr::first(Arr::first($e->errors())));
    }
}

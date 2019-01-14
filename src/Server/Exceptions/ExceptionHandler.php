<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-09 20:04
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Server\Exceptions;

use Exception;
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
     *
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        return ! $this->shouldntReport($e);
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
        $dontReport = array_merge($this->dontReport, $this->internalDontReport);

        return ! is_null(Arr::first($dontReport, function ($type) use ($e) {
            return $e instanceof $type;
        }));
    }

    /**
     * @param RequestContract $request
     * @param Exception       $e
     *
     * @return Response|null|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        if ($this->isServiceException($e)) {
            $e->setRequest($request);
        } elseif ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e);
        }

        return $this->prepareJsonResponse($e);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param Exception                                         $e
     */
    public function renderForConsole($output, Exception $e)
    {
        (new ConsoleApplication())->renderException($e, $output);
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
        return config('app.debug') ? [
            'message'   => $e->getMessage(),
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'message' => $this->isServiceException($e) ? $e->getMessage() : 'Server Error',
        ];
    }

    /**
     * @param Exception $e
     *
     * @return Response
     */
    protected function prepareJsonResponse(Exception $e)
    {
        return new Response(
            $this->packResponseError($this->convertExceptionToArray($e)),
            $e->getCode() <= 0 ? 500 : $e->getCode()
        );
    }

    /**
     * @param ValidationException $e
     *
     * @return Response|null|\Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e)
    {
        if ($e->response) {
            return $e->response;
        }

        return new Response($this->packResponseError([
            'message' => $e->getMessage(),
            'errors'  => $e->errors(),
        ]), $e->status);
    }

    /**
     * @param array $data
     */
    protected function packResponseError(array $data)
    {
        return $data;
        //return $this->container->make('server.packer')->pack($data);
    }
}

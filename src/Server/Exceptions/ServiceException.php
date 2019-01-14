<?php

namespace CrCms\Microservice\Server\Exceptions;

use Throwable;
use RuntimeException;
use CrCms\Microservice\Server\Contracts\RequestContract;
use CrCms\Microservice\Server\Contracts\ResponseContract;

/**
 * Class ServiceException.
 */
class ServiceException extends RuntimeException
{
    /**
     * @var RequestContract
     */
    protected $request;

    /**
     * @var ResponseContract
     */
    protected $response;

    /**
     * ServiceException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param RequestContract $request
     *
     * @return ServiceException
     */
    public function setRequest(RequestContract $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return RequestContract
     */
    public function getRequest(): RequestContract
    {
        return $this->request;
    }

    /**
     * @return ResponseContract
     */
    public function getResponse(): ResponseContract
    {
        return $this->response;
    }

    /**
     * @param ResponseContract $response
     *
     * @return ServiceException
     */
    public function setResponse(ResponseContract $response): self
    {
        $this->response = $response;

        return $this;
    }
}

<?php

namespace CrCms\Microservice\Routing;

use Closure;
use Exception;
use Throwable;
use Illuminate\Pipeline\Pipeline as BasePipeline;
use CrCms\Microservice\Server\Contracts\RequestContract;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

/**
 * This extended pipeline catches any exceptions that occur during each slice.
 *
 * The exceptions are converted to HTTP responses for proper middleware handling.
 */
class Pipeline extends BasePipeline
{
    /**
     * Get the final piece of the Closure onion.
     *
     * @param \Closure $destination
     *
     * @return \Closure
     */
    protected function prepareDestination(Closure $destination)
    {
        return function ($passable) use ($destination) {
            try {
                return $destination($passable);
            } catch (Exception $e) {
                return $this->handleException($passable, $e);
            } catch (Throwable $e) {
                return $this->handleException($passable, new FatalThrowableError($e));
            }
        };
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return \Closure
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    $slice = parent::carry();

                    $callable = $slice($stack, $pipe);

                    return $callable($passable);
                } catch (Exception $e) {
                    return $this->handleException($passable, $e);
                } catch (Throwable $e) {
                    return $this->handleException($passable, new FatalThrowableError($e));
                }
            };
        };
    }

    /**
     * Handle the given exception.
     *
     * @param mixed      $passable
     * @param \Exception $e
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected function handleException($passable, Exception $e)
    {
        if (! $this->container->bound(ExceptionHandlerContract::class) ||
            ! $passable instanceof RequestContract) {
            throw $e;
        }

        $handler = $this->container->make(ExceptionHandlerContract::class);

        $handler->report($e);

        $response = $handler->render($passable, $e);

        if (method_exists($response, 'withException')) {
            $response->withException($e);
        }

        return $response;
    }
}

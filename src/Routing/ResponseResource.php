<?php

namespace CrCms\Microservice\Routing;

use Traversable;
use JsonSerializable;
use InvalidArgumentException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use CrCms\Foundation\Resources\Resource;
use CrCms\Microservice\Server\Http\Response;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class ResponseResource.
 */
class ResponseResource
{
    /**
     * @param null $content
     *
     * @return Response
     */
    public function created($content = null): Response
    {
        $response = new Response($content);
        $response->setStatusCode(201);

        return $response;
    }

    /**
     * @param null $content
     *
     * @return Response
     */
    public function accepted($content = null): Response
    {
        $response = new Response($content);
        $response->setStatusCode(202);

        return $response;
    }

    /**
     * @return Response
     */
    public function noContent(): Response
    {
        $response = new Response(null);

        return $response->setStatusCode(204);
    }

    /**
     * @param $collection
     * @param string $collect
     * @param array  $fields
     * @param array  $includes
     *
     * @return JsonResponse
     */
    public function collection($collection, string $collect = '', array $fields = [], array $includes = []): JsonResponse
    {
        if (! $collection instanceof ResourceCollection && class_exists($collect)) {
            if (substr($collect, -8) === 'Resource') {
                $collection = call_user_func([$collect, 'collection'], $collection);
            } elseif (substr($collect, -10) === 'Collection') {
                $collection = (new $collect($collection));
            } else {
                throw new InvalidArgumentException('Non-existent resource converter');
            }
        }

        if (! $collection instanceof ResourceCollection) {
            throw new InvalidArgumentException('Non-existent resource converter');
        }

        return $this->resourceToResponse($collection, $fields, $includes);
    }

    /**
     * @param $resource
     * @param string $collect
     * @param array  $fields
     * @param array  $includes
     *
     * @return JsonResponse
     */
    public function resource($resource, string $collect = '', array $fields = [], array $includes = []): JsonResponse
    {
        if (! $resource instanceof Resource && class_exists($collect)) {
            $resource = (new $collect($resource));
        }

        if (! $resource instanceof Resource) {
            throw new InvalidArgumentException('Non-existent resource converter');
        }

        return $this->resourceToResponse($resource, $fields, $includes);
    }

    /**
     * @param ResourceCollection|resource $resource
     * @param array                       $fields
     * @param array                       $includes
     *
     * @return JsonResponse
     */
    public function resourceToResponse($resource, array $fields, array $includes = []): JsonResponse
    {
        if ($includes && $resource instanceof Resource) {
            $resource->setIncludes($includes);
        }

        if (isset($fields['only'])) {
            $type = 'only';
            $fields = $fields['only'];
        } elseif (isset($fields['except']) || isset($fields['hide'])) {
            $type = 'except';
            $fields = $fields['except'] ?? $fields['hide'];
        } else {
            $type = 'except';
        }

        return $resource->$type($fields)->response();
    }

    /**
     * @param $paginator
     * @param string $collect
     * @param array  $fields
     * @param array  $includes
     *
     * @return JsonResponse
     */
    public function paginator($paginator, string $collect = '', array $fields = [], array $includes = []): JsonResponse
    {
        return $this->collection($paginator, $collect, $fields, $includes);
    }

    /**
     * @param array $array
     *
     * @return Response
     */
    public function array(array $array): JsonResponse
    {
        return new JsonResponse($array);
    }

    /**
     * @param $data
     *
     * @return JsonResponse
     */
    public function json($data): JsonResponse
    {
        return new JsonResponse($data, 200);
    }

    /**
     * @param array|Collection|\JsonSerializable|\Traversable $data
     * @param string                                          $key
     *
     * @return JsonResponse
     */
    public function data($data, string $key = 'data'): JsonResponse
    {
        if (is_array($data)) {
        } elseif ($data instanceof Collection) {
            $data = $data->all();
        } elseif ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        } elseif ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        } else {
            throw new InvalidArgumentException('Incorrect parameter format');
        }

        return $this->array([$key => $data]);
    }
}

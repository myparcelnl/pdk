<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Contract\ModelInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for version-specific response formatters.
 *
 * Provides common response creation with proper versioning headers.
 */
abstract class AbstractVersionedResource implements VersionedResourceInterface
{
    protected Arrayable $model; // TODO: use union type ModelInterface|Arrayable when PHP 8.0 is minimum

    public function __construct(Arrayable $model)
    {
        $this->model = $model;
    }

    /**
     * Format the resource for usage in the endpoint response.
     * Base implementation just returns the model as an array.
     * @return array
     */
    public function format(): array
    {
        return $this->model->toArray();
    }

    /**
     * Create a versioned response with properly formatted data.
     */
    public function createResponse(Request $request, int $status = 200): Response
    {
        $version = static::getVersion();

        // Create JSON response with versioned headers following ADR-0011
        $response = new JsonResponse($this->format(), $status);

        // Set Content-Type header with version
        $response->headers->set('Content-Type', "application/json; version={$version}");

        // Set Accept header to indicate this version is supported
        $response->headers->set('Accept', "application/json; version={$version}");

        return $response;
    }
}

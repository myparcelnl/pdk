<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

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
    /**
     * Create a versioned response with properly formatted data.
     */
    public static function createResponse($data, Request $request, int $status = 200): Response
    {
        $version = static::getVersion();
        $formattedData = static::format($data);

        // Create JSON response with versioned headers following ADR-0011
        $response = new JsonResponse($formattedData, $status);

        // Set Content-Type header with version
        $response->headers->set('Content-Type', "application/json; version={$version}");

        // Set Accept header to indicate this version is supported
        $response->headers->set('Accept', "application/json; version={$version}");

        return $response;
    }
}

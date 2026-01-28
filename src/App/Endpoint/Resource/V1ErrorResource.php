<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Resource;

use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;

/**
 * Generic error resource for API v1 responses.
 *
 * Provides standardized error formatting for all v1 API endpoints.
 */
class V1ErrorResource extends AbstractVersionedResource
{
    /**
     * Get the API version this resource handles.
     */
    public static function getVersion(): int
    {
        return 1;
    }

    /**
     * Format error data for v1 API response.
     *
     * @param mixed $data Error data containing type, title, status, detail, instance, and errors
     */
    public static function format($data): array
    {
        // Ensure we have the required error structure
        return [
            'type'     => $data['type'] ?? 'https://errors.myparcel/generic-error',
            'title'    => $data['title'] ?? 'Error',
            'status'   => $data['status'] ?? 500,
            'detail'   => $data['detail'] ?? 'An error occurred',
            'instance' => $data['instance'] ?? null,
            'errors'   => $data['errors'] ?? [],
        ];
    }
}

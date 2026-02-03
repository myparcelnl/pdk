<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use MyParcelNL\Pdk\App\Endpoint\Resource\ProblemDetails;
use MyParcelNL\Pdk\App\Endpoint\Resource\V1ErrorResource;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for all API v1 request handlers.
 *
 * Provides a reusable validation error response method following v1 API standards.
 */
abstract class AbstractV1Request extends AbstractVersionedRequest
{
    /**
     * Get the API version this request handles.
     */
    public static function getVersion(): int
    {
        return 1;
    }
}

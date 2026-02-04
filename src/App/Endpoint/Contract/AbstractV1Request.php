<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use Symfony\Component\HttpFoundation\Response;
/**
 * Base class for all API v1 request handlers.
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

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Resource;

use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractVersionedResource;

/**
 * Base implementation to use ProblemDetails as a resource.
 *
 * @property ProblemDetails $model
 */
final class ProblemDetailsV1Resource extends AbstractVersionedResource
{
    public static function getVersion(): int
    {
        return 1;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Contract\ModelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contract for version-specific response formatting resources.
 *
 * Each implementation handles one API version's response format.
 */
interface VersionedResourceInterface
{
    public function __construct(Arrayable $model);

    /**
     * Get the API version this resource handles.
     */
    public static function getVersion(): int;

    /**
     * Format the json for the instantiated model for this resource's version.
     */
    public function format(): array;

    /**
     * Create a versioned response with properly formatted data.
     */
    public function createResponse(Request $request, int $status = 200): Response;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contract for version-specific response formatting resources.
 * 
 * Each implementation handles one API version's response format.
 */
interface VersionedResourceInterface
{
    /**
     * Get the API version this resource handles.
     */
    public static function getVersion(): int;

    /**
     * Format the given data for this resource's version.
     *
     * @param mixed $data
     */
    public static function format($data): array;

    /**
     * Create a versioned response with properly formatted data.
     *
     * @param mixed                                      $data
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int                                        $status
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public static function createResponse($data, Request $request, int $status = 200): Response;
}

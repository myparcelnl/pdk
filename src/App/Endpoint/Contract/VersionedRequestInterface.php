<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contract for version-specific request validators and processors.
 *
 * Each implementation handles one API version's request format and validation.
 */
interface VersionedRequestInterface
{
    /**
     * Get the API version this request handles.
     */
    public static function getVersion(): int;

    /**
     * Validate the request according to this version's rules.
     */
    public function validate(): bool;

    /**
     * Get validation errors if validation fails.
     */
    public function getValidationErrors(): array;

    /**
     * Create an error response for validation failures.
     */
    public function createValidationErrorResponse(): Response;
}

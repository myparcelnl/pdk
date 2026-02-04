<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base contract and implementation for all PDK endpoint handlers.
 *
 * Provides version detection, request parsing, validation helpers, and defines
 * the interface for creating versioned requests and responses to reduce duplication.
 */
abstract class AbstractEndpoint
{
    /**
     * Handle the endpoint request.
     */
    abstract public function handle(Request $request): Response;

    /**
     * Get all API versions supported by this endpoint.
     *
     * @return int[]
     */
    abstract public function getSupportedVersions(): array;

    /**
     * Create a versioned request object based on API version.
     */
    abstract public function createVersionedRequest(Request $request, int $version): VersionedRequestInterface;

    /**
     * Create a versioned resource response based on API version.
     */
    abstract public function createVersionedResource(Arrayable $model, int $version): VersionedResourceInterface;

    /**
     * Default implementation returns true. Override for custom validation.
     */
    public function validate(Request $request): bool
    {
        return true;
    }

    /**
     * Extract JSON body from request.
     */
    protected function getRequestBody(Request $request): array
    {
        $content = $request->getContent();

        if (empty($content)) {
            return [];
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Detect API version from request headers following ADR-0011.
     */
    protected function detectVersion(Request $request): int
    {
        // Try Content-Type header first (takes precedence per ADR-0011)
        $contentTypeHeader = $request->headers->get('Content-Type', '');
        $contentTypeVersion = $this->extractVersionFromHeader($contentTypeHeader);

        if ($contentTypeVersion !== null) {
            return $contentTypeVersion;
        }

        // Try Accept header as fallback
        $acceptHeader = $request->headers->get('Accept', '');
        $acceptVersion = $this->extractVersionFromHeader($acceptHeader);

        return $acceptVersion ?? 1; // Default to v1
    }

    /**
     * Extract version parameter from header value.
     * Only extracts the major version number, ignoring prefixes and suffixes.
     * Examples: v1.0.0 => 1, version=2.1-beta => 2
     */
    protected function extractVersionFromHeader(string $header): ?int
    {
        if (preg_match('/version=v?(\d+)/', $header, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}

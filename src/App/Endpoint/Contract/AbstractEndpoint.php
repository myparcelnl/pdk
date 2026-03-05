<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use MyParcelNL\Pdk\App\Endpoint\ProblemDetails;
use MyParcelNL\Pdk\App\Endpoint\Resource\ProblemDetailsV1Resource;
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
     * Handle the endpoint request with automatic version detection and error handling.
     * All version-related exceptions are handled automatically.
     */
    final public function handle(Request $request): Response
    {
        try {
            $version = $this->detectVersion($request);
            return $this->handleVersionedRequest($request, $version);
        } catch (UnsupportedVersionException $exception) {
            return $this->createUnsupportedVersionResponse($request, $exception);
        }
    }

    /**
     * Handle the actual endpoint logic with a validated API version.
     * Concrete endpoints should implement their business logic here.
     */
    abstract protected function handleVersionedRequest(Request $request, int $version): Response;

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
     * Detect API version from request headers following ADR-0011.
     *
     * @throws UnsupportedVersionException When requested version is not supported
     */
    protected function detectVersion(Request $request): int
    {
        // Try Content-Type header first (takes precedence per ADR-0011)
        $contentTypeHeader = $request->headers->get('Content-Type', '');
        $contentTypeVersion = $this->extractVersionFromHeader($contentTypeHeader);

        if ($contentTypeVersion !== null) {
            $this->validateSupportedVersion($contentTypeVersion);
            return $contentTypeVersion;
        }

        // Try Accept header as fallback
        $acceptHeader = $request->headers->get('Accept', '');
        $acceptVersion = $this->extractVersionFromHeader($acceptHeader);

        if ($acceptVersion !== null) {
            $this->validateSupportedVersion($acceptVersion);
            return $acceptVersion;
        }

        // Default to v1 and validate it's supported
        $defaultVersion = 1;
        $this->validateSupportedVersion($defaultVersion);
        return $defaultVersion;
    }

    /**
     * Extract version parameter from header value.
     * Only extracts the major version number, ignoring prefixes and suffixes.
     * Examples: v1.0.0 => 1, version=2.1-beta => 2
     */
    protected function extractVersionFromHeader(string $header): ?int
    {
        if (preg_match('/version=v?(\d+)/i', $header, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Validate that the requested version is supported by this endpoint.
     *
     * @throws UnsupportedVersionException When version is not supported
     */
    protected function validateSupportedVersion(int $version): void
    {
        $supportedVersions = $this->getSupportedVersions();

        if (!in_array($version, $supportedVersions, true)) {
            throw new UnsupportedVersionException(
                sprintf(
                    'API version %d is not supported. Supported versions: %s',
                    $version,
                    implode(', ', $supportedVersions)
                ),
                $version,
                $supportedVersions
            );
        }
    }

    /**
     * Create a 406 Not Acceptable response for unsupported API versions.
     * As per ADR-0011, unsupported version requests must return HTTP 406.
     */
    protected function createUnsupportedVersionResponse(Request $request, UnsupportedVersionException $exception): Response
    {
        $supportedVersionsText = implode(', ', $exception->getSupportedVersions());
        $detail = sprintf(
            'API version %d is not supported. Supported versions: %s',
            $exception->getRequestedVersion(),
            $supportedVersionsText
        );

        $resource = new ProblemDetailsV1Resource(
            new ProblemDetails(null, 'Not Acceptable', Response::HTTP_NOT_ACCEPTABLE, $detail)
        );

        return $resource->createResponse($request, Response::HTTP_NOT_ACCEPTABLE);
    }
}

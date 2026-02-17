<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use Exception;

/**
 * Exception thrown when an unsupported API version is requested.
 *
 * As per ADR-0011, this should result in an HTTP 406 Not Acceptable response.
 */
class UnsupportedVersionException extends Exception
{
    private int $requestedVersion;
    private array $supportedVersions;

    public function __construct(string $message, int $requestedVersion, array $supportedVersions)
    {
        parent::__construct($message);
        $this->requestedVersion = $requestedVersion;
        $this->supportedVersions = $supportedVersions;
    }

    /**
     * Get the version that was requested.
     */
    public function getRequestedVersion(): int
    {
        return $this->requestedVersion;
    }

    /**
     * Get the versions supported by the endpoint.
     */
    public function getSupportedVersions(): array
    {
        return $this->supportedVersions;
    }

    /**
     * Get the HTTP status code for this exception (406 Not Acceptable).
     */
    public function getHttpStatusCode(): int
    {
        return 406;
    }
}

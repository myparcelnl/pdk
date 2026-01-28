<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for version-specific request handlers.
 * 
 * Provides common validation utilities and error response generation.
 */
abstract class AbstractVersionedRequest implements VersionedRequestInterface
{
    protected Request $httpRequest;
    protected array $validationErrors = [];

    public function __construct(Request $httpRequest)
    {
        $this->httpRequest = $httpRequest;
    }

    /**
     * Get validation errors if validation fails.
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Create an error response for validation failures.
     * Note: This method should be implemented by specific request classes
     * that know which resource class to use for their version.
     */
    abstract public function createValidationErrorResponse(): Response;

    /**
     * Get a summary validation error message.
     */
    protected function getValidationErrorMessage(): string
    {
        if (empty($this->validationErrors)) {
            return 'Request validation failed';
        }

        return sprintf(
            'Request validation failed: %s',
            implode(', ', array_keys($this->validationErrors))
        );
    }

    /**
     * Add a validation error.
     */
    protected function addValidationError(string $field, string $message): void
    {
        $this->validationErrors[$field] = $message;
    }

    /**
     * Extract JSON body from request.
     */
    protected function getRequestBody(): array
    {
        $content = $this->httpRequest->getContent();

        if (empty($content)) {
            return [];
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : [];
    }
}

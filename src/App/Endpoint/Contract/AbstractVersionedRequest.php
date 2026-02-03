<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;
use MyParcelNL\Pdk\App\Endpoint\ProblemDetails;
use MyParcelNL\Pdk\App\Endpoint\Resource\ProblemDetailsV1Resource;
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
     */
    public function createValidationErrorResponse(): Response
    {
        $resource = new ProblemDetailsV1Resource(
            new ProblemDetails(null, 'Invalid Request', Response::HTTP_BAD_REQUEST, $this->getValidationErrorMessage())
        );

        return $resource->createResponse($this->httpRequest, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Helper to generate a 404 not found response
     * @param string $detail
     * @return Response
     */
    public function createNotFoundErrorResponse(string $detail = 'Resource not found'): Response
    {
        $resource = new ProblemDetailsV1Resource(
            new ProblemDetails(null, 'Not Found', Response::HTTP_NOT_FOUND, $detail)
        );

        return $resource->createResponse($this->httpRequest, Response::HTTP_NOT_FOUND);
    }

    public function createInternalServerErrorResponse(string $detail = 'Internal Server Error'): Response
    {
        $resource = new ProblemDetailsV1Resource(
            new ProblemDetails(null, 'Internal Server Error', Response::HTTP_INTERNAL_SERVER_ERROR, $detail)
        );

        return $resource->createResponse($this->httpRequest, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

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

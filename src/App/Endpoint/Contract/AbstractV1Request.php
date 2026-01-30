<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use MyParcelNL\Pdk\App\Endpoint\Resource\V1ErrorResource;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for all API v1 request handlers.
 *
 * Provides a reusable validation error response method following v1 API standards.
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

    /**
     * Create a standardized v1 validation error response.
     */
    public function createValidationErrorResponse(): Response
    {
        $errorData = [
            'type'     => 'https://errors.myparcel/validation-error',
            'title'    => 'Validation Error',
            'status'   => 400,
            'detail'   => $this->getValidationErrorMessage(),
            'instance' => $this->httpRequest->getPathInfo(),
            'errors'   => $this->validationErrors,
        ];

        return V1ErrorResource::createResponse($errorData, $this->httpRequest, 400);
    }

    /**
     * Create a standardized v1 not found error response.
     */
    public function createNotFoundErrorResponse(string $detail, string $instance): Response
    {
        $errorData = [
            'type'     => 'https://errors.myparcel/not-found',
            'title'    => 'Not Found',
            'status'   => 404,
            'detail'   => $detail,
            'instance' => $instance,
        ];

        return V1ErrorResource::createResponse($errorData, $this->httpRequest, 404);
    }

    /**
     * Create a standardized v1 internal server error response.
     */
    public function createInternalServerErrorResponse(string $instance): Response
    {
        $errorData = [
            'type'     => 'https://errors.myparcel/internal-error',
            'title'    => 'Internal Server Error',
            'status'   => 500,
            'detail'   => 'An unexpected error occurred while processing the request',
            'instance' => $instance,
        ];

        return V1ErrorResource::createResponse($errorData, $this->httpRequest, 500);
    }
}

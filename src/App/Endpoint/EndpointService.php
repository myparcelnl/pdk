<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractEndpoint;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractV1Request;
use MyParcelNL\Pdk\App\Endpoint\Contract\EndpointServiceInterface;
use MyParcelNL\Pdk\App\Endpoint\EndpointRegistry;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Main service for processing PDK endpoint requests.
 *
 * Orchestrates request validation, handler resolution, and error handling.
 * Integrates with PDK configuration and dependency injection systems.
 *
 * @since 3.1.0
 */
class EndpointService implements EndpointServiceInterface
{
    /**
     * Handle an endpoint request.
     */
    public function handleRequest(Request $request, EndpointRegistry $endpoint): Response
    {
        $handlerClass = $endpoint->getHandlerClass();
        $logContext = [
            'endpoint' => $handlerClass,
            'method'   => $request->getMethod(),
            'uri'      => $request->getRequestUri(),
        ];

        Logger::debug('Processing endpoint request', $logContext);

        try {
            $handler = $this->resolveEndpoint($handlerClass);

            $logContext['handler'] = get_class($handler);

            if (! $handler->validate($request)) {
                Logger::debug('Endpoint request validation failed', $logContext);
                return $this->createValidationErrorResponse($request);
            }

            $response = $handler->handle($request);
            Logger::debug('Endpoint request processed successfully', $logContext);

            return $response;
        } catch (InvalidArgumentException $exception) {
            Logger::error('Unknown endpoint', $logContext + ['error' => $exception->getMessage()]);
            return $this->createNotFoundErrorResponse($request, $handlerClass, $exception);
        } catch (Throwable $exception) {
            Logger::error('Endpoint processing failed', $logContext + ['error' => $exception->getMessage()]);
            return $this->createInternalServerErrorResponse($request);
        }
    }

    /**
     * Resolve endpoint handler from class name.
     */
    protected function resolveEndpoint(string $handlerClass): AbstractEndpoint
    {
        if (!class_exists($handlerClass)) {
            throw new InvalidArgumentException("Endpoint handler class not found: $handlerClass");
        }

        $handler = Pdk::get($handlerClass);

        if (!$handler instanceof AbstractEndpoint) {
            throw new InvalidArgumentException("Class $handlerClass does not implement AbstractEndpoint");
        }

        return $handler;
    }

    /**
     * Create a validation error response using version-appropriate formatting.
     */
    private function createValidationErrorResponse(Request $request): Response
    {
        $version = $this->detectVersionFromRequest($request);

        // For now, we only support v1, but this can be extended for future versions
        if ($version === 1) {
            $v1Request = Pdk::get(AbstractV1Request::class);
            return $v1Request->createValidationErrorResponse($request);
        }

        // Fallback to v1 for unknown versions
        $v1Request = Pdk::get(AbstractV1Request::class);
        return $v1Request->createValidationErrorResponse($request);
    }

    /**
     * Create a not found error response using version-appropriate formatting.
     */
    private function createNotFoundErrorResponse(Request $request, string $handlerClass, InvalidArgumentException $exception): Response
    {
        $version = $this->detectVersionFromRequest($request);
        $detail = sprintf('Endpoint handler "%s" not found', $handlerClass);

        // For now, we only support v1, but this can be extended for future versions
        if ($version === 1) {
            $v1Request = Pdk::get(AbstractV1Request::class);
            return $v1Request->createNotFoundErrorResponse($detail, $request->getPathInfo());
        }

        // Fallback to v1 for unknown versions
        $v1Request = Pdk::get(AbstractV1Request::class);
        return $v1Request->createNotFoundErrorResponse($detail, $request->getPathInfo());
    }

    /**
     * Create an internal server error response using version-appropriate formatting.
     */
    private function createInternalServerErrorResponse(Request $request): Response
    {
        $version = $this->detectVersionFromRequest($request);

        // For now, we only support v1, but this can be extended for future versions
        if ($version === 1) {
            $v1Request = Pdk::get(AbstractV1Request::class);
            return $v1Request->createInternalServerErrorResponse($request->getPathInfo());
        }

        // Fallback to v1 for unknown versions
        $v1Request = Pdk::get(AbstractV1Request::class);
        return $v1Request->createInternalServerErrorResponse($request->getPathInfo());
    }

    /**
     * Detect API version from request headers (similar to AbstractEndpoint).
     */
    private function detectVersionFromRequest(Request $request): int
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
     * Extract version parameter from header value (similar to AbstractEndpoint).
     */
    private function extractVersionFromHeader(string $header): ?int
    {
        if (preg_match('/version=v?(\d+)/', $header, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}

# PDK Endpoint System

The PDK Endpoint system provides a type-safe, versioned API architecture for handling external requests for data that's offered by the PDK.

## Architecture Overview

```
External Controller → EndpointService → Endpoint Handler → Versioned Request/Response
```

The system consists of:

- **AbstractEndpoint**: Base class that merges interface and implementation, defines abstract methods for creating versioned requests and responses to reduce duplication
- **EndpointService**: Main service for routing and processing requests using type-safe endpoint identification
- **EndpointRegistry**: Type-safe registry class mapping constants directly to handler classes
- **Endpoint Handlers**: Specific endpoint implementations in the `Handler/` subdirectory (e.g., `GetDeliveryOptionsEndpoint`)
- **Versioned Resources**: Single-version response formatters implementing `VersionedResourceInterface`
- **Versioned Requests**: Single-version request validators implementing `VersionedRequestInterface`
  - **AbstractV1Request**: Specialized base class for v1 requests providing standardized validation error responses
  - **V1ErrorResource**: Generic error formatter for consistent v1 API error responses

## Architecture Decision Records (ADRs)

This endpoint system is governed by several ADRs that define architectural patterns and constraints:

- **[ADR-0011: Header-based API Versioning]({{ADR-0011-LINK}})** - Defines version negotiation through HTTP headers
- **[ADR-XXXX: Endpoint Architecture Pattern]({{ADR-ENDPOINT-ARCHITECTURE-LINK}})** - Service pattern replacing manager pattern
- **[ADR-XXXX: Single-Version Resource Design]({{ADR-SINGLE-VERSION-LINK}})** - Each resource handles exactly one API version
- **[ADR-XXXX: Type-Safe Endpoint Identification]({{ADR-TYPE-SAFE-ENDPOINTS-LINK}})** - Enum-based endpoint constants

> **Note**: Replace placeholder links above with actual ADR URLs when available.

## API Documentation

### OpenAPI Specifications

- **[Delivery Options v1 API](openapi-delivery-options-v1.yaml)** - Complete OpenAPI 3.0 specification for the delivery options endpoint

## Quick Start

### Setting Up a New Endpoint within the PDK

Follow these steps to create a new endpoint:

#### 1. Add Endpoint Identifier

Add your endpoint constant to `EndpointRegistry.php`:

```php
class EndpointRegistry
{
    public const DELIVERY_OPTIONS = GetDeliveryOptionsEndpoint::class;
    public const SHIPMENTS = GetShipmentsEndpoint::class; // ← Add new constant

    public static function shipments(): self
    {
        return new self(self::SHIPMENTS); // ← Add factory method
    }

    public static function getValidClasses(): array
    {
        return [
            self::DELIVERY_OPTIONS,
            self::SHIPMENTS, // ← Add to valid classes
        ];
    }
}
```

#### 2. Create Endpoint Handler

Create your main endpoint class extending `AbstractEndpoint` in the `Handler/` subdirectory:

Example:

```php
<?php
// src/App/Endpoint/Handler/GetShipmentsEndpoint.php

namespace MyParcelNL\Pdk\App\Endpoint\Handler;

/**
 * Endpoint handler for retrieving shipment data.
 *
 * Fetches and formats shipments using version-specific handlers.
 */
class GetShipmentsEndpoint extends AbstractEndpoint
{
    public function handle(Request $request): Response
    {
        $version = $this->detectVersion($request);
        $versionedRequest = $this->createVersionedRequest($request, $version);

        if (!$versionedRequest->validate()) {
            return $versionedRequest->createValidationErrorResponse();
        }

        // Your business logic here
        $data = $this->processRequest($versionedRequest);

        $resource = $this->createVersionedResourceResponse($data, $version);
        return $resource::createResponse($data, $request);
    }

    protected function createVersionedRequest(Request $request, int $version): VersionedRequestInterface
    {
        return match($version) {
            1 => new GetShipmentsV1Request($request),
            default => new GetShipmentsV1Request($request),
        };
    }

    protected function createVersionedResourceResponse(array $data, int $version): VersionedResourceInterface
    {
        $resourceClass = match($version) {
            1 => ShipmentsV1Resource::class,
            default => ShipmentsV1Resource::class,
        };

        return new $resourceClass();
    }
}
```

#### 3. Create Version-Specific Request Handler

For v1 API requests, extend `AbstractV1Request` to get standardized validation error responses:

```php
<?php
// src/App/Endpoint/Request/GetShipmentsV1Request.php

use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractV1Request;

/**
 * API v1 request validator for shipments requests.
 */
class GetShipmentsV1Request extends AbstractV1Request
{
    private ?string $orderId = null;

    public function validate(): bool
    {
        $this->validationErrors = [];

        $this->orderId = $this->extractOrderId();

        if (!$this->orderId) {
            $this->addValidationError('orderId', 'Missing required parameter: orderId');
        }

        return empty($this->validationErrors);
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    private function extractOrderId(): ?string
    {
        // Extract from query, route params, or request body
        return $this->httpRequest->query->get('orderId')
            ?? $this->httpRequest->attributes->get('orderId')
            ?? $this->getRequestBody()['orderId'] ?? null;
    }
}
```

> **Note**: `AbstractV1Request` automatically provides:
>
> - `getVersion()` returning '1'
> - `createValidationErrorResponse()` using standardized v1 error format
> - Consistent error response structure across all v1 endpoints

#### 4. Create Version-Specific Resource Formatter

```php
<?php
// src/App/Endpoint/Resource/ShipmentsV1Resource.php

/**
 * API v1 response formatter for shipments data.
 */
class ShipmentsV1Resource extends AbstractVersionedResource
{
    public static function getVersion(): int
    {
        return 1;
    }

    public static function format($data): array
    {
        if (isset($data['type'])) {
            // Error response - pass through
            return $data;
        }

        // Format success response
        return [
            'orderId' => $data['orderId'],
            'shipments' => array_map([self::class, 'formatShipment'], $data['shipments'] ?? []),
        ];
    }

    private static function formatShipment(array $shipment): array
    {
        return [
            'id' => $shipment['id'],
            'trackingNumber' => $shipment['tracking_number'],
            'status' => $shipment['status'],
            'createdAt' => $shipment['created_at'],
        ];
    }
}
```

That's it! The endpoint is now ready to use. No additional configuration is needed since the constant maps directly to the handler class.

### Integrating into External Applications

#### WordPress Plugin Integration

```php
<?php
// In your WordPress plugin

use MyParcelNL\Pdk\App\Endpoint\EndpointService;
use MyParcelNL\Pdk\App\Endpoint\PdkEndpoint;

class MyParcelWordPressController
{
    private EndpointService $endpointService;

    public function __construct()
    {
        $this->endpointService = new EndpointService();

        // Register WordPress REST routes
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        register_rest_route('myparcel/v1', '/delivery-options', [
            'methods' => 'GET',
            'callback' => [$this, 'handleDeliveryOptions'],
        ]);

        register_rest_route('myparcel/v1', '/shipments', [
            'methods' => 'GET',
            'callback' => [$this, 'handleShipments'],
        ]);
    }

    public function handleDeliveryOptions(WP_REST_Request $wpRequest): WP_REST_Response
    {
        // Convert WordPress request to Symfony request
        $request = $this->convertToSymfonyRequest($wpRequest);

        // Process through PDK
        $response = $this->endpointService->handleRequest(
            $request,
            EndpointRegistry::deliveryOptions()
        );

        // Convert back to WordPress response
        return new WP_REST_Response(
            json_decode($response->getContent(), true),
            $response->getStatusCode()
        );
    }

    private function convertToSymfonyRequest(WP_REST_Request $wpRequest): Request
    {
        return new Request(
            $wpRequest->get_query_params(),
            $wpRequest->get_body_params(),
            [],
            [],
            [],
            $this->getServerFromWordPress(),
            $wpRequest->get_body()
        );
    }
}
```

#### Laravel Integration

```php
<?php
// In your Laravel application

use MyParcelNL\Pdk\App\Endpoint\EndpointService;
use MyParcelNL\Pdk\App\Endpoint\PdkEndpoint;

class MyParcelController extends Controller
{
    private EndpointService $endpointService;

    public function __construct(EndpointService $endpointService)
    {
        $this->endpointService = $endpointService;
    }

    public function getDeliveryOptions(Request $request): JsonResponse
    {
        $response = $this->endpointService->handleRequest(
            $request,
            EndpointRegistry::deliveryOptions()
        );

        return response()->json(
            json_decode($response->getContent(), true),
            $response->getStatusCode()
        );
    }

    public function getShipments(Request $request): JsonResponse
    {
        $response = $this->endpointService->handleRequest(
            $request,
            EndpointRegistry::shipments()
        );

        return response()->json(
            json_decode($response->getContent(), true),
            $response->getStatusCode()
        );
    }
}
```

#### Generic PHP Integration

```php
<?php
// In any PHP application

use MyParcelNL\Pdk\App\Endpoint\EndpointService;
use MyParcelNL\Pdk\App\Endpoint\EndpointRegistry;
use Symfony\Component\HttpFoundation\Request;

class MyParcelApiHandler
{
    private EndpointService $endpointService;

    public function __construct()
    {
        $this->endpointService = new EndpointService();
    }

    public function handleApiRequest(string $endpoint, array $queryParams = []): array
    {
        // Create Symfony request from PHP globals
        $request = Request::createFromGlobals();

        // Or create manually
        $request = new Request($queryParams);

        try {
            // Determine endpoint
            $pdkEndpoint = match($endpoint) {
                'delivery-options' => EndpointRegistry::deliveryOptions(),
                'shipments' => EndpointRegistry::shipments(),
                default => throw new InvalidArgumentException("Unknown endpoint: $endpoint")
            };

            // Process request
            $response = $this->endpointService->handleRequest($request, $pdkEndpoint);

            return [
                'status' => $response->getStatusCode(),
                'data' => json_decode($response->getContent(), true)
            ];

        } catch (Exception $e) {
            return [
                'status' => 500,
                'data' => ['error' => $e->getMessage()]
            ];
        }
    }
}
```

## API Versioning

API versioning follows **[ADR-0011: Header-based API Versioning]({{ADR-0011-LINK}})** specifications.

### Request Headers

Clients specify the API version using headers:

```http
# Content-Type takes precedence
Content-Type: application/json; version=1

# Accept header as fallback
Accept: application/json; version=1
```

### Response Headers

The service automatically sets version headers:

```http
Content-Type: application/json; version=1
Accept: application/json; version=1
```

## Error Handling

All endpoints return structured errors following RFC 7807 (as defined in **[ADR-XXXX: Error Response Format]({{ADR-ERROR-FORMAT-LINK}})**):

```json
{
  "type": "https://errors.myparcel/validation-error",
  "title": "Validation Error",
  "status": 400,
  "detail": "Request validation failed: orderId",
  "instance": "/api/delivery-options",
  "errors": {
    "orderId": "Missing required parameter: orderId"
  }
}
```

## Testing

Use the provided test patterns:

```php
it('processes endpoint request successfully', function () {
    $mockEndpoint = mock(EndpointInterface::class)
        ->shouldReceive('validate')->andReturn(true)
        ->shouldReceive('handle')->andReturn(new Response('{"success": true}', 200))
        ->getMock();

    // No configuration needed - endpoint maps directly to class
    Pdk::shouldReceive('get')
        ->with(GetShipmentsEndpoint::class)
        ->andReturn($mockEndpoint);

    $service = new EndpointService();
    $response = $service->handleRequest($request, EndpointRegistry::shipments());

    expect($response->getStatusCode())->toBe(200);
});
```

## Best Practices

Following the architectural decisions outlined in the ADRs above:

1. **Always use EndpointRegistry enum** - Never use raw strings (see **[ADR-XXXX: Type-Safe Endpoint Identification]({{ADR-TYPE-SAFE-ENDPOINTS-LINK}})**)
2. **Version everything** - Plan for API evolution from day one (see **[ADR-0011: Header-based API Versioning]({{ADR-0011-LINK}})**)
3. **Single-version resources** - Each resource handles exactly one API version (see **[ADR-XXXX: Single-Version Resource Design]({{ADR-SINGLE-VERSION-LINK}})**)
4. **Handle errors gracefully** - Follow RFC 7807 format
5. **Log appropriately** - Use PDK Logger for debugging
6. **Test comprehensively** - Mock dependencies, test error cases

# PDK Endpoint System

The PDK Endpoint system provides a type-safe, versioned API architecture for handling external requests for data that's offered by the PDK.

## Architecture Overview

```
External Controller → Endpoint Handler → Versioned Request/Response
```

The system consists of:

- **AbstractEndpoint**: Base class that merges interface and implementation, defines abstract methods for creating versioned requests and responses to reduce duplication
- **EndpointRegistry**: Simple static registry providing handler class constants
- **Endpoint Handlers**: Specific endpoint implementations in the `Handler/` subdirectory (e.g., `GetDeliveryOptionsEndpoint`)
- **Versioned Resources**: Single-version response formatters implementing `VersionedResourceInterface`
- **Versioned Requests**: Single-version request validators implementing `VersionedRequestInterface`
  - **AbstractV1Request**: Specialized base class for v1 requests providing standardized validation error responses
- **ProblemDetails**: RFC 9457 compliant error response value object
- **ProblemDetailsResource**: Resource formatter for error responses

## Architecture Decision Records (ADRs)

This endpoint system is governed by several ADRs that define architectural patterns and constraints:

- **[ADR-0011: Header-based API Versioning](https://github.com/mypadev/engineering-adr/blob/main/01-adr/0014-api-design-standards.md)** - Defines version negotiation through HTTP headers
- **[RFC 9457: Problem Details for HTTP APIs](https://www.rfc-editor.org/rfc/rfc9457.html)** - Standardized error response format

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

    public static function all(): array
    {
        return [
            self::DELIVERY_OPTIONS,
            self::SHIPMENTS, // ← Add to all() method
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
        $model = Shipment::getById($versionedRequest->getShipmentId());

        $resource = $this->createVersionedResource($model, $version);
        return $resource->createResponse($request);
    }

    public function createVersionedRequest(Request $request, int $version): VersionedRequestInterface
    {
        return match($version) {
            1 => new GetShipmentsV1Request($request),
            default => new GetShipmentsV1Request($request),
        };
    }

    public function createVersionedResource(Arrayable $model, int $version): VersionedResourceInterface
    {
        $resourceClass = match($version) {
            1 => ShipmentsV1Resource::class,
            default => ShipmentsV1Resource::class,
        };

        return new $resourceClass($model);
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
    private ?string $shipmentId = null;

    public function validate(): bool
    {
        $this->validationErrors = [];

        $this->shipmentId = $this->extractShipmentId();

        if (!$this->shipmentId) {
            $this->addValidationError('shipmentId', 'Missing required parameter: shipmentId');
        }

        return empty($this->validationErrors);
    }

    public function getShipmentId(): ?string
    {
        return $this->shipmentId;
    }

    private function extractShipmentId(): ?string
    {
        // Extract from query, route params, or request body
        return $this->httpRequest->query->get('shipmentId')
            ?? $this->httpRequest->attributes->get('shipmentId')
            ?? $this->getRequestBody()['shipmentId'] ?? null;
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
 *
 * @property ShipmentCollection $model
 */
class ShipmentsV1Resource extends AbstractVersionedResource
{
    public function __construct(ShipmentCollection $model)
    {
        parent::__construct($model);
    }

    public static function getVersion(): int
    {
        return 1;
    }

    public function format(): array
    {
        // Format the model data for v1 API response
        return [
            'shipments' => $this->model->map(function ($shipment) {
                return [
                    'id' => $shipment->id,
                    'trackingNumber' => $shipment->trackingNumber,
                    'status' => $shipment->status,
                    'createdAt' => $shipment->createdAt?->format('c'),
                ];
            })->toArray(),
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

use MyParcelNL\Pdk\App\Endpoint\EndpointRegistry;
use MyParcelNL\Pdk\App\Endpoint\Handler\GetDeliveryOptionsEndpoint;
use MyParcelNL\Pdk\Facade\Pdk;

class MyParcelWordPressController
{
    public function __construct()
    {
        // Register WordPress REST routes
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        register_rest_route('myparcel/v1', '/delivery-options', [
            'methods' => 'GET',
            'callback' => [$this, 'handleDeliveryOptions'],
        ]);
    }

    public function handleDeliveryOptions(WP_REST_Request $wpRequest): WP_REST_Response
    {
        // Convert WordPress request to Symfony request
        $request = $this->convertToSymfonyRequest($wpRequest);

        // Get handler from PDK DI container using EndpointRegistry
        $handler = Pdk::get(EndpointRegistry::DELIVERY_OPTIONS);

        // Handle request directly
        $response = $handler->handle($request);

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

#### Generic Symfony Integration

```php
<?php
// In any PHP application

use MyParcelNL\Pdk\App\Endpoint\EndpointRegistry;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\Request;

class MyParcelApiHandler
{
    public function handleApiRequest(string $endpoint, array $queryParams = []): array
    {
        // Create Symfony request from PHP globals
        $request = Request::createFromGlobals();

        // Or create manually
        $request = new Request($queryParams);

        try {
            // Determine endpoint handler class
            $handlerClass = match($endpoint) {
                'delivery-options' => EndpointRegistry::DELIVERY_OPTIONS,
                default => throw new InvalidArgumentException("Unknown endpoint: $endpoint")
            };

            // Get handler from PDK DI container
            $handler = Pdk::get($handlerClass);

            // Process request
            $response = $handler->handle($request);

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

All endpoints return structured errors following RFC 9457 Problem Details for HTTP APIs:

```json
{
  "type": "https://errors.myparcel/validation-error",
  "title": "Validation Error",
  "status": 400,
  "detail": "Request validation failed: orderId"
}
```

The `ProblemDetails` value object and `ProblemDetailsResource` provide RFC 9457 compliant error responses.

## Testing

Use the provided test patterns:

```php
it('handles delivery options request successfully', function () {
    // Mock dependencies
    $mockOrder = mock(PdkOrder::class);
    $mockOrder->deliveryOptions = mock(DeliveryOptions::class);

    $mockRepository = mock(PdkOrderRepositoryInterface::class)
        ->shouldReceive('get')->with('123')->andReturn($mockOrder)
        ->getMock();

    Pdk::shouldReceive('get')
        ->with(PdkOrderRepositoryInterface::class)
        ->andReturn($mockRepository);

    // Get handler and test directly
    $handler = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);
    $response = $handler->handle($request);

    expect($response->getStatusCode())->toBe(200);
});

it('validates request parameters', function () {
    $handler = new GetDeliveryOptionsEndpoint();

    $validRequest = new Request(['orderId' => '123']);
    expect($handler->validate($validRequest))->toBeTrue();

    $invalidRequest = new Request();
    expect($handler->validate($invalidRequest))->toBeFalse();
});
```

## Best Practices

Following the architectural decisions outlined in the ADRs above:

1. **Always use EndpointRegistry constants** - Use `EndpointRegistry::DELIVERY_OPTIONS` to get handler class names
2. **Version everything** - Plan for API evolution from day one (see **[ADR-0011: Header-based API Versioning]({{ADR-0011-LINK}})**)
3. **Single-version resources** - Each resource handles exactly one API version
4. **Handle errors gracefully** - Use `ProblemDetails` and `ProblemDetailsResource` following RFC 9457
5. **Log appropriately** - Use PDK Logger for debugging
6. **Test comprehensively** - Mock dependencies, test error cases, test versioned requests and resources

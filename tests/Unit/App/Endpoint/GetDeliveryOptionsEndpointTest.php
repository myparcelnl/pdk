<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint;

use GuzzleHttp\Psr7\Response;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use MyParcelNL\Pdk\App\Endpoint\Handler\GetDeliveryOptionsEndpoint;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\RetailLocationType;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockExceptionPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockNotFoundPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use Symfony\Component\HttpFoundation\Request;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('returns delivery options for valid order id', function () {
    // Create and store a mock order using the factory pattern
    factory(PdkOrder::class)
        ->withExternalIdentifier('123')
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withDate('2024-01-15')
                ->withPackageType('PACKAGE')
        )
        ->store();

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(200);

    $content = json_decode($response->getContent(), true);
    expect($content)->toBeArray();
    expect($content)->toHaveKey('carrier', 'POSTNL');
    expect($content)->toHaveKey('date');
    expect($content)->toHaveKey('packageType', 'PACKAGE');
});

it('returns 400 error when order id is missing', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request();

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(400);

    $content = json_decode($response->getContent(), true);
    expect($content)->toHaveKey('type');
    expect($content)->toHaveKey('title');
    expect($content)->toHaveKey('status');
    expect($content['status'])->toBe(400);
    expect($content)->toHaveKey('detail');
    expect($content['detail'])->toContain('orderId');
});

it('validates request correctly', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();

    // Valid request with orderId
    $validRequest = new Request(['orderId' => '123']);
    expect($endpoint->validate($validRequest))->toBeTrue();

    // Invalid request without orderId
    $invalidRequest = new Request();
    expect($endpoint->validate($invalidRequest))->toBeFalse();
});

it('extracts order id from query parameter', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '456']);

    expect($endpoint->validate($request))->toBeTrue();
});

it('returns 404 error when order not found', function () {
    // Recreate PDK instance with a repository that always returns null for find()
    MockPdkFactory::create([
        PdkOrderRepositoryInterface::class => autowire(MockNotFoundPdkOrderRepository::class),
    ]);

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '999']);

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(404);

    $content = json_decode($response->getContent(), true);
    expect($content['status'])->toBe(404);
    expect($content['detail'])->toContain('Order not found');

    // Reset PDK instance so subsequent tests get the default mock
    Pdk::setPdkInstance(null);
    MockPdkFactory::create();
});


it('logs an error and returns a 500 error when an exception occurs', function () {
    // Recreate PDK instance with a repository that throws an exception
    MockPdkFactory::create([
        PdkOrderRepositoryInterface::class => autowire(MockExceptionPdkOrderRepository::class),
    ]);

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(500);

    $content = json_decode($response->getContent(), true);
    expect($content['status'])->toBe(500);
    expect($content['detail'])->toContain('Internal server error');

    // Reset PDK instance so subsequent tests get the default mock
    Pdk::setPdkInstance(null);
    MockPdkFactory::create();
});

it('detects API version from request headers', function () {
    // Create and store a mock order
    factory(PdkOrder::class)
        ->withExternalIdentifier('123')
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
        )
        ->store();

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);
    $request->headers->set('Content-Type', 'application/json; version=1');

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('Content-Type'))->toContain('version=1');
});

it('createVersionedRequest() falls back to v1 for unsupported versions', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);

    $versionedRequest = $endpoint->createVersionedRequest($request, 99);

    expect($versionedRequest)->toBeInstanceOf(\MyParcelNL\Pdk\App\Endpoint\Request\GetDeliveryOptionsV1Request::class);
});

it('createVersionedResource() falls back to v1 for unsupported versions', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $deliveryOptions = factory(DeliveryOptions::class)->withCarrier('POSTNL')->make();

    $resource = $endpoint->createVersionedResource($deliveryOptions, 99);

    expect($resource)->toBeInstanceOf(\MyParcelNL\Pdk\App\Endpoint\Resource\DeliveryOptionsV1Resource::class);
});

it('returns 406 Not Acceptable when unsupported API version is requested', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);
    $request->headers->set('Content-Type', 'application/json; version=2'); // v2 not supported

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(406);

    $content = json_decode($response->getContent(), true);
    expect($content)->toHaveKey('type', null);
    expect($content)->toHaveKey('title', 'Not Acceptable');
    expect($content)->toHaveKey('status', 406);
    expect($content)->toHaveKey('detail');
    expect($content['detail'])->toContain('API version 2 is not supported');
    expect($content['detail'])->toContain('Supported versions: 1');
});

it('handles unsupported version before validating orderId parameter', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(); // Missing orderId, but unsupported version should take priority
    $request->headers->set('Accept', 'application/json; version=5');

    $response = $endpoint->handle($request);

    // Should return 406 for unsupported version, not 400 for missing orderId
    expect($response->getStatusCode())->toBe(406);

    $content = json_decode($response->getContent(), true);
    expect($content['status'])->toBe(406);
    expect($content['detail'])->toContain('API version 5 is not supported');
});

// Instantiate validator outside of tests so it is only built once, since building the validator is expensive. We can reuse it across tests since the schema does not change.
$openApiValidator = (new ValidatorBuilder())
    ->fromYamlFile(__DIR__ . '/../../../../src/App/Endpoint/openapi-delivery-options-v1.yaml')
    ->getResponseValidator();

it('returns a response which matches the openApi schema', function (string $packageTypeName, string $deliveryTypeName, string $retailLocationType) use ($openApiValidator) {
    $allShipmentOptions = [];
    foreach (ShipmentOptions::ALL_SHIPMENT_OPTIONS as $option) {
        if ($option === ShipmentOptions::SIGNATURE) {
            // Value should be insured amount
            $allShipmentOptions[$option] = 1000;
        } else {
            $allShipmentOptions[$option] = true;
        }
    }

    // Create and store a mock order with all shipment options, a date and a pickup location to fully test the schema
    factory(PdkOrder::class)
        ->withExternalIdentifier('123')
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withDate('2024-01-15T10:30:00+00:00')
                ->withPackageType($packageTypeName)
                ->withDeliveryType($deliveryTypeName)
                ->withShipmentOptions($allShipmentOptions)
                ->withPickupLocation(
                    factory(RetailLocation::class)
                        ->withName('Pickup Location 1')
                        ->withStreet('Main Street')
                        ->withPostalCode('12345')
                        ->withCity('Anytown')
                        ->withCountry('NL')
                        ->withType(new RetailLocationType($retailLocationType))
                )
        )
        ->store();

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);
    $request->headers->set('Content-Type', 'application/json; version=1');

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(200);

    // Validate response against OpenAPI schema
    $operation  = new OperationAddress('/delivery-options', 'get');

    // Convert Symfony Response to PSR-7 Response using Guzzle for validation
    $psr7Response = new Response(
        $response->getStatusCode(),
        $response->headers->all(),
        $response->getContent()
    );

    // Try - catch the validate, so we can print the validation errors if it fails
    try {
        $openApiValidator->validate($operation, $psr7Response);
    } catch (InvalidBody $e) {
        $this->fail($e->getVerboseMessage());
    }
})->with('packageTypeNames', 'deliveryTypeNames', 'retailLocationTypes');

it('insurance: calls calculate() on the options service when handling a delivery options request', function () {
    // Spy test: verifies the endpoint calls PdkOrderOptionsServiceInterface::calculate(), which runs
    // the full orderCalculators chain including InsuranceCalculator.
    //
    // Root-cause: the endpoint previously called calculateShipmentOptions() only, which resolves
    // boolean TriState flags from settings but never runs InsuranceCalculator. Insurance was left
    // as TriState ENABLED (1), and the formatter produced 1 * 1_000_000 = 1_000_000.
    /** @var \Mockery\MockInterface&PdkOrderOptionsServiceInterface $spyService */
    $spyService = mock(PdkOrderOptionsServiceInterface::class);
    $spyService->shouldReceive('calculate')->once()->andReturnUsing(function (PdkOrder $order) {
        return $order;
    });
    $spyService->shouldReceive('calculateShipmentOptions')->andReturnUsing(function (PdkOrder $order) {
        return $order;
    });

    MockPdkFactory::create([PdkOrderOptionsServiceInterface::class => $spyService]);
    TestBootstrapper::hasAccount();

    factory(PdkOrder::class)
        ->withExternalIdentifier('insurance-spy-order')
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier('POSTNL'))
        ->store();

    $endpoint = new GetDeliveryOptionsEndpoint();
    $endpoint->handle(new Request(['orderId' => 'insurance-spy-order']));

    // Mockery verifies calculate() was called exactly once.
    // If the fix is reverted to calculateShipmentOptions(), this assertion fails.
    $this->addToAssertionCount(1); // We will assert the method call via Mockery, so we need to tell Pest about it

    // Reset the factory so other tests are not affected by the spy
    MockPdkFactory::create();
});

it('insurance: resolves to exportInsuranceUpTo amount in micro-units when shipment options insurance is INHERIT and carrier has enabled insurance', function () {
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)->withCarrier('POSTNL')->withInsurance(0, 0, 100000))
        )
        ->store();

    factory(PdkOrder::class)
        ->withExternalIdentifier('insurance-order')
        ->withLines([factory(PdkOrderLine::class)->withPrice(100000)])
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withShipmentOptions(factory(ShipmentOptions::class)->withInsurance(TriStateService::INHERIT))
        )
        ->store();

    factory(Settings::class)
        ->withCarrier('POSTNL', [
            CarrierSettings::EXPORT_INSURANCE        => true,
            CarrierSettings::EXPORT_INSURANCE_UP_TO => 500,
        ])
        ->store();

    $endpoint = new GetDeliveryOptionsEndpoint();
    $response = $endpoint->handle(new Request(['orderId' => 'insurance-order']));

    expect($response->getStatusCode())->toBe(200);

    $content = json_decode($response->getContent(), true);

    // After TriStateOptionCalculator resolves INHERIT → ENABLED (1), InsuranceCalculator treats
    // the value as an explicit amount. Tier lookup: first capabilities tier ≥ 1 = 50000. Carrier max = 100000.
    expect($content['shipmentOptions']['insurance']['amount'])->toBe(50000 * 1_000_000);

    Pdk::get(PdkSettingsRepositoryInterface::class)->reset();
});

it('insurance: omits insurance from the response when exportInsurance is disabled in carrier settings', function () {
    factory(PdkOrder::class)
        ->withExternalIdentifier('insurance-disabled-order')
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier('POSTNL'))
        ->store();

    factory(Settings::class)
        ->withCarrier('POSTNL', [
            CarrierSettings::EXPORT_INSURANCE        => false,
            CarrierSettings::EXPORT_INSURANCE_UP_TO => 500,
        ])
        ->store();

    $endpoint = new GetDeliveryOptionsEndpoint();
    $response = $endpoint->handle(new Request(['orderId' => 'insurance-disabled-order']));

    expect($response->getStatusCode())->toBe(200);

    $content = json_decode($response->getContent(), true);
    expect($content['shipmentOptions'])->not()->toHaveKey('insurance');

    Pdk::get(PdkSettingsRepositoryInterface::class)->reset();
});

it('insurance: preserves an explicit monetary amount already set on the order shipment options', function () {
    factory(PdkOrder::class)
        ->withExternalIdentifier('insurance-explicit-order')
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withShipmentOptions(factory(ShipmentOptions::class)->withInsurance(10000))
        )
        ->store();

    factory(Settings::class)
        ->withCarrier('POSTNL', [
            CarrierSettings::EXPORT_INSURANCE        => true,
            CarrierSettings::EXPORT_INSURANCE_UP_TO => 500,
        ])
        ->store();

    $endpoint = new GetDeliveryOptionsEndpoint();
    $response = $endpoint->handle(new Request(['orderId' => 'insurance-explicit-order']));

    expect($response->getStatusCode())->toBe(200);

    $content = json_decode($response->getContent(), true);

    // The explicit amount (10000) resolves to the nearest capabilities tier (50000).
    expect($content['shipmentOptions']['insurance']['amount'])->toBe(50000 * 1_000_000);

    Pdk::get(PdkSettingsRepositoryInterface::class)->reset();
});

<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\Account\Model\AccountGeneralSettings;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollectionFactory;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkPhysicalProperties;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddressFactory;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettingsFactory;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Collection\CustomsDeclarationItemCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\RetailLocationFactory;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkV2Response;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrdersResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsValidationErrorResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use MyParcelNL\Pdk\Tests\Uses\UsesSettingsMock;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock(), new UsesApiMock(), new UsesNotificationsMock(), new UsesSettingsMock());

dataset('order mode toggle', [
    'default'    => [false],
    'order mode' => [true],
]);

dataset('action type toggle', [
    'auto'   => 'automatic',
    'manual' => 'manual',
]);

/**
 * Helpers
 */
/**
 * Returns the shipment options array from the API request body, accounting for order vs shipment mode.
 */
function getRequestOptions(array $body, bool $orderMode, int $idx = 0): array
{
    return $orderMode
        ? ($body['data']['orders'][$idx]['shipment']['options'] ?? [])
        : ($body['data']['shipments'][$idx]['options'] ?? []);
}

/**
 * In order mode all option keys are always present (disabled ones === 0). Asserts that the target
 * option equals 1 and every other integer-valued option (except structural keys) equals 0.
 */
function assertOnlyOptionEnabled(array $options, string $targetKey): void
{
    $excluded = ['delivery_type', 'package_type', 'delivery_date', 'label_description', 'insurance'];

    expect($options[$targetKey])->toBe(1);

    foreach ($options as $key => $value) {
        if ($key === $targetKey || in_array($key, $excluded, true)) {
            continue;
        }
        if (is_int($value)) {
            expect($value)->toBe(0, "Expected option '{$key}' to be 0 when only '{$targetKey}' is enabled");
        }
    }
}

/**
 * Sets up a generic all-capabilities carrier, stores the given settings, fires the export and
 * returns the API request body.
 */
function exportWithSetting(bool $orderMode, CarrierSettingsFactory $carrierSettingsFactory): array
{
    $fakeCarrier = factory(Carrier::class)
        ->withCarrier('POSTNL') // Carrier needs to actually exist in the maps to ID, so lets use POSTNL as a default here
        ->withAllCapabilities()
        ->make();

    factory(Settings::class)
        ->withOrder(factory(OrderSettings::class)->withOrderMode($orderMode))
        ->withCarrier($fakeCarrier->carrier, $carrierSettingsFactory)
        ->store();

    $collection = factory(PdkOrderCollection::class)
        ->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)->withCarrier($fakeCarrier)
                )
        )
        ->store()
        ->make();

    MockApi::enqueue(
        ...$orderMode
            ? [new ExamplePostOrdersResponse(), new ExamplePostOrderNotesResponse()]
            : [new ExamplePostShipmentsResponse()]
    );

    Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => Arr::pluck($collection->toArray(), 'externalIdentifier'),
    ]);

    $lastRequest = MockApi::ensureLastRequest();

    return json_decode($lastRequest->getBody()->getContents(), true);
}

it('handles auto exported flag', function (?string $actionType) {
    $orderFactory = factory(PdkOrderCollection::class)->push(
        factory(PdkOrder::class)->toTheNetherlands()
    );
    $orders       = new Collection($orderFactory->make());

    $orderFactory->store();

    MockApi::enqueue(new ExamplePostShipmentsResponse());
    MockApi::enqueue(new ExampleGetShipmentLabelsLinkResponse());
    MockApi::enqueue(new ExamplePostShipmentsResponse());

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'actionType' => $actionType,
        'orderIds'   => $orders
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    expect(json_decode($response->getContent(), false)->data->orders[0]->autoExported)->toBe(
        'automatic' === $actionType
    );

    // if it was already auto-exported, you can not auto-export again
    $orderFactory = factory(PdkOrderCollection::class)->push(
        factory(PdkOrder::class)->toTheNetherlands()->withAutoExported('automatic' === $actionType)
    );
    $orders       = new Collection($orderFactory->make());

    $orderFactory->store();

    MockApi::enqueue(new ExamplePostShipmentsResponse());

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'actionType' => 'automatic',
        'orderIds'   => $orders
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    expect(count(json_decode($response->getContent(), false)->data->orders[0]->shipments))->toBe('automatic' === $actionType ? 0 : 1);

    // if it was already auto-exported, you can manually export it no problem
    $orderFactory = factory(PdkOrderCollection::class)->push(
        factory(PdkOrder::class)->toTheNetherlands()->withAutoExported('automatic' === $actionType)
    );
    $orders       = new Collection($orderFactory->make());

    $orderFactory->store();

    MockApi::enqueue(new ExamplePostShipmentsResponse());

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'actionType' => 'manual',
        'orderIds'   => $orders
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    expect(count(json_decode($response->getContent(), false)->data->orders[0]->shipments))->toBe(1);
})
    ->with('action type toggle');

/**
 * Per-setting assertion tests.
 * Each test uses order mode toggle (shipment + order mode) and a generic all-capabilities carrier.
 * No real carrier names are referenced — capabilities come entirely from the factory.
 */

it('exports order with signature setting enabled', function (bool $orderMode) {
    $body    = exportWithSetting($orderMode, factory(CarrierSettings::class)->withExportSignature(true));
    $options = getRequestOptions($body, $orderMode);

    assertOnlyOptionEnabled($options, 'signature');
})
    ->with('order mode toggle');

it('exports order with age check setting enabled', function (bool $orderMode) {
    $body    = exportWithSetting($orderMode, factory(CarrierSettings::class)->withExportAgeCheck(true));
    $options = getRequestOptions($body, $orderMode);

    // age_check forces only_recipient and signature on via calculator cascade
    expect($options['age_check'])->toBe(1)
        ->and($options['only_recipient'])->toBe(1)
        ->and($options['signature'])->toBe(1);

    $cascadeKeys = ['age_check', 'only_recipient', 'signature'];
    $excluded    = ['delivery_type', 'package_type', 'delivery_date', 'label_description', 'insurance'];
    foreach ($options as $key => $value) {
        if (in_array($key, $cascadeKeys, true) || in_array($key, $excluded, true)) {
            continue;
        }
        if (is_int($value)) {
            expect($value)->toBe(0, "Expected option '{$key}' to be 0 when age_check is enabled");
        }
    }
})
    ->with('order mode toggle');

it('exports order with return setting enabled', function (bool $orderMode) {
    $body    = exportWithSetting($orderMode, factory(CarrierSettings::class)->withExportReturn(true));
    $options = getRequestOptions($body, $orderMode);

    assertOnlyOptionEnabled($options, 'return');
})
    ->with('order mode toggle');

it('exports order with large format setting enabled', function (bool $orderMode) {
    $body    = exportWithSetting($orderMode, factory(CarrierSettings::class)->withExportLargeFormat(true));
    $options = getRequestOptions($body, $orderMode);

    assertOnlyOptionEnabled($options, 'large_format');
})
    ->with('order mode toggle');

it('exports order with only recipient setting enabled', function (bool $orderMode) {
    $body    = exportWithSetting($orderMode, factory(CarrierSettings::class)->withExportOnlyRecipient(true));
    $options = getRequestOptions($body, $orderMode);

    assertOnlyOptionEnabled($options, 'only_recipient');
})
    ->with('order mode toggle');

it('exports order with hide sender setting enabled', function (bool $orderMode) {
    $body    = exportWithSetting($orderMode, factory(CarrierSettings::class)->withExportHideSender(true));
    $options = getRequestOptions($body, $orderMode);

    assertOnlyOptionEnabled($options, 'hide_sender');
})
    ->with('order mode toggle');

it('exports order with receipt code setting enabled', function (bool $orderMode) {
    $body    = exportWithSetting($orderMode, factory(CarrierSettings::class)->withExportReceiptCode(true));
    $options = getRequestOptions($body, $orderMode);

    assertOnlyOptionEnabled($options, 'receipt_code');
})
    ->with('order mode toggle');

it('exports order with insurance setting enabled', function (bool $orderMode) {
    $fakeCarrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->make();

    factory(Settings::class)
        ->withOrder(factory(OrderSettings::class)->withOrderMode($orderMode))
        ->withCarrier(
            $fakeCarrier->carrier,
            factory(CarrierSettings::class)
                ->withExportInsurance(true)
                ->withExportInsuranceFromAmount(0)
                ->withExportInsuranceUpTo(10000)
        )
        ->store();

    $collection = factory(PdkOrderCollection::class)
        ->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($fakeCarrier))
                ->withLines(factory(PdkOrderLineCollection::class, 1)->eachWith(['priceAfterVat' => 5000]))
        )
        ->store()
        ->make();

    MockApi::enqueue(
        ...$orderMode
            ? [new ExamplePostOrdersResponse(), new ExamplePostOrderNotesResponse()]
            : [new ExamplePostShipmentsResponse()]
    );

    Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => Arr::pluck($collection->toArray(), 'externalIdentifier'),
    ]);

    $lastRequest = MockApi::ensureLastRequest();
    $body        = json_decode($lastRequest->getBody()->getContents(), true);
    $options     = getRequestOptions($body, $orderMode);

    expect($options)->toHaveKey('insurance')
        ->and($options['insurance']['amount'])->toBeGreaterThan(0);
})
    ->with('order mode toggle');

it('does not add export options when carrier lacks the capability', function (bool $orderMode) {
    // Build a carrier with NO options capabilities — only package + delivery types
    $fakeCarrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withPackageTypes([RefShipmentPackageTypeV2::PACKAGE])
        ->withDeliveryTypes([RefTypesDeliveryTypeV2::STANDARD])
        ->withOptions([]) // no shipment options at all
        ->make();

    // Enable ALL carrier export settings — none should appear in the request body
    factory(CarrierSettings::class, $fakeCarrier->carrier)
        ->withExportSignature(true)
        ->withExportAgeCheck(true)
        ->withExportReturn(true)
        ->withExportLargeFormat(true)
        ->withExportOnlyRecipient(true)
        ->withExportHideSender(true)
        ->store();

    factory(Settings::class)
        ->withOrder(factory(OrderSettings::class)->withOrderMode($orderMode))
        ->store();

    $collection = factory(PdkOrderCollection::class)
        ->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier($fakeCarrier))
        )
        ->store()
        ->make();

    MockApi::enqueue(
        ...$orderMode
            ? [new ExamplePostOrdersResponse(), new ExamplePostOrderNotesResponse()]
            : [new ExamplePostShipmentsResponse()]
    );

    Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => Arr::pluck($collection->toArray(), 'externalIdentifier'),
    ]);

    $lastRequest      = MockApi::ensureLastRequest();
    $body             = json_decode($lastRequest->getBody()->getContents(), true);
    $options          = getRequestOptions($body, $orderMode);
    $capabilityOptions = ['signature', 'age_check', 'return', 'large_format', 'only_recipient', 'hide_sender'];

    if (! $orderMode) {
        // In shipment mode: option keys only appear when set — none should be present
        foreach ($capabilityOptions as $key) {
            expect($options)->not->toHaveKey($key);
        }
    } else {
        // In order mode all keys appear but every capability option should be 0
        foreach ($capabilityOptions as $key) {
            if (array_key_exists($key, $options)) {
                expect($options[$key])->toBe(0, "Expected option '{$key}' to be 0 for carrier with no capabilities");
            }
        }
    }
})
    ->with('order mode toggle');

it('exports order with return large format setting enabled without adding options', function (bool $orderMode) {
    // return_large_format is a UI-only configuration for the return label; it has no export implementation
    $body    = exportWithSetting($orderMode, factory(CarrierSettings::class)->withExportReturnLargeFormat(true));
    $options = getRequestOptions($body, $orderMode);

    if (! $orderMode) {
        expect($options)->not->toHaveKey('return')
            ->and($options)->not->toHaveKey('large_format');
    } else {
        expect($options['return'])->toBe(0)
            ->and($options['large_format'])->toBe(0);
    }
})
    ->with('order mode toggle');

/**
 * Multi-shipment batch test.
 * Uses 3 orders in one batch and asserts per-shipment options based on what was explicitly
 * configured on each order. Carrier capabilities come from the default shop setup.
 */
it('exports multiple orders in a batch with per-shipment option resolution', function () {
    factory(Settings::class)->store();

    $orderFactory = factory(PdkOrderCollection::class)
        ->push(
            // Order 0: PostNL mailbox — package type should be MAILBOX (2), no delivery-specific options
            factory(PdkOrder::class)
                ->withDeliveryOptions([
                    'carrier'     => RefCapabilitiesSharedCarrierV2::POSTNL,
                    'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                ])
                ->withLines(factory(PdkOrderLineCollection::class, 1)->eachWith(['quantity' => 5])),
            // Order 1: PostNL evening with explicit signature + only_recipient
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                        ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
                        ->withDate('2077-10-23 09:47:51')
                        ->withShipmentOptions(
                            factory(ShipmentOptions::class)
                                ->withOnlyRecipient(TriStateService::ENABLED)
                                ->withSignature(TriStateService::ENABLED)
                        )
                ),
            // Order 2: DHL For You with explicit age_check, hide_sender, signature
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withCarrier(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU)
                        ->withShipmentOptions(
                            factory(ShipmentOptions::class)
                                ->withAgeCheck(TriStateService::ENABLED)
                                ->withHideSender(TriStateService::ENABLED)
                                ->withSignature(TriStateService::ENABLED)
                        )
                )
        );

    $orders = $orderFactory->make();
    $orderFactory->store();

    MockApi::enqueue(new ExamplePostShipmentsResponse());

    Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => $orders->pluck('externalIdentifier')->toArray(),
    ]);

    $lastRequest = MockApi::ensureLastRequest();
    $body        = json_decode($lastRequest->getBody()->getContents(), true);
    $shipments   = $body['data']['shipments'];

    expect($shipments)->toHaveLength(3);

    // Order 0: mailbox package type, no signature
    expect($shipments[0]['options']['package_type'])->toBe(DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID)
        ->and($shipments[0]['options'])->not->toHaveKey('signature');

    // Order 1: evening delivery type, signature and only_recipient present
    expect($shipments[1]['options']['delivery_type'])->toBe(DeliveryOptions::DELIVERY_TYPE_EVENING_ID)
        ->and($shipments[1]['options']['signature'])->toBe(1)
        ->and($shipments[1]['options']['only_recipient'])->toBe(1);

    // Order 2: DHL with age_check, hide_sender, signature all set
    expect($shipments[2]['options']['age_check'])->toBe(1)
        ->and($shipments[2]['options']['hide_sender'])->toBe(1)
        ->and($shipments[2]['options']['signature'])->toBe(1);
});

/**
 * Asserts response shape of the action itself and no export errors.
 */
it('exports orders and returns correct action response shape', function (
    bool                      $orderMode,
    CarrierSettingsFactory    $carrierSettingsFactory,
    PdkOrderCollectionFactory $orderFactory
) {
    $orders = $orderFactory->make();
    $orderFactory->store();

    $carriers = $orders
        ->pluck('deliveryOptions.carrier.carrier')
        ->toArray();

    factory(Settings::class)
        ->withOrder(factory(OrderSettings::class)->withOrderMode($orderMode))
        ->withCarriers($carriers, $carrierSettingsFactory)
        ->store();

    MockApi::enqueue(
        ...$orderMode
            ? [new ExamplePostOrdersResponse(), new ExamplePostOrderNotesResponse()]
            : [new ExamplePostShipmentsResponse()]
    );

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => $orders
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    $content = json_decode($response->getContent(), true);

    $responseOrders    = $content['data']['orders'];
    $responseShipments = Arr::pluck($responseOrders, 'shipments');

    $errors = Notifications::all()
        ->filter(function (Notification $notification) {
            return $notification->variant === Notification::VARIANT_ERROR;
        });

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($responseOrders)
        ->toHaveLength(count($orders))
        ->and($response->getStatusCode())
        ->toBe(200)
        // Expect no errors to have been added to notifications
        ->and($errors->toArrayWithoutNull())
        ->toBe([]);

    if ($orderMode) {
        expect($responseShipments)->each->toHaveLength(0);
    } else {
        expect($responseShipments)->each->toHaveLength(1)
            ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
    }
})
    ->with('order mode toggle') // data sets defined in "tests/Datasets"
    ->with('carrier export settings')
    ->with('pdk orders domestic');

it('merges partial payload with existing order', function (
    bool                      $orderMode,
    CarrierSettingsFactory    $carrierSettingsFactory,
    PdkOrderCollectionFactory $orderFactory
) {

    $orders = new Collection($orderFactory->make());

    $orderFactory->store();

    $carriers = $orders
        ->pluck('deliveryOptions.carrier.carrier')
        ->toArray();

    factory(Settings::class)
        ->withOrder(factory(OrderSettings::class)->withOrderMode($orderMode))
        ->withCarriers($carriers, $carrierSettingsFactory)
        ->store();

    MockApi::enqueue(
        ...$orderMode
            ? [new ExamplePostOrdersResponse(), new ExamplePostOrderNotesResponse()]
            : [new ExamplePostShipmentsResponse()]
    );

    $date = new \DateTime('+1 day');

    /**
     * @var DeliveryOptions $existingDeliveryOptions
     */
    $existingDeliveryOptions = $orders->pluck('deliveryOptions')->first();
    $partialDeliveryOptions = [
        DeliveryOptions::DATE => $date
    ];
    $mergedDeliveryOptions = $existingDeliveryOptions
        ->fill($partialDeliveryOptions)
        ->toArrayWithoutNull();

    expect($mergedDeliveryOptions[DeliveryOptions::DATE])->toBe($date->format(Pdk::get('defaultDateFormat')));

    /**
     * @var PdkPhysicalProperties $existingPhysicalProperties
     */
    $existingPhysicalProperties = $orders->pluck('physicalProperties')->first();
    $partialPhysicalProperties  = [
        'manualWeight' => 500,
    ];
    $mergedPhysicalProperties = $existingPhysicalProperties
        ->fill($partialPhysicalProperties);

    expect($mergedPhysicalProperties['manualWeight'])->toBe($partialPhysicalProperties['manualWeight']);

    $requestWithPayload = new Request(
        ['action' => PdkBackendActions::EXPORT_ORDERS, 'orderIds' => $orders->pluck('externalIdentifier')->toArray()],
        [],
        [],
        [],
        [],
        [],
        json_encode(
            [
                'data' => [
                    'orders' => [
                        [
                            'deliveryOptions' => $partialDeliveryOptions,
                            'physicalProperties' => $partialPhysicalProperties
                        ],
                    ],
                ],
            ]
        )
    );

    $response = Actions::execute($requestWithPayload);

    $content = json_decode($response->getContent(), true);

    $responseOrders    = $content['data']['orders'];
    $responseShipments = Arr::pluck($responseOrders, 'shipments');

    $errors = Notifications::all()
        ->filter(function (Notification $notification) {
            return $notification->variant === Notification::VARIANT_ERROR;
        });

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($responseOrders)
        ->toHaveLength(count($orders))
        // Check to make sure the carrier did not reset to the default - this is the only part that is easy to test due to not being affected by calculators
        ->and($responseOrders[0]['deliveryOptions'][DeliveryOptions::CARRIER])
        ->toBe($mergedDeliveryOptions[DeliveryOptions::CARRIER])
        ->and($responseOrders[0]['physicalProperties'][DeliveryOptions::CARRIER] ?? null)
        ->toBe($mergedPhysicalProperties[DeliveryOptions::CARRIER] ?? null)
        ->and($response->getStatusCode())
        ->toBe(200)
        // Expect no errors to have been added to notifications
        ->and($errors->toArrayWithoutNull())
        ->toBe([]);

    if ($orderMode) {
        expect($responseShipments)->each->toHaveLength(0);
    } else {
        expect($responseShipments)->each->toHaveLength(1)
            ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
    }
})
    ->with('order mode toggle')
    ->with('carrier export settings')
    ->with('pdk orders domestic');;

it('exports multicollo order', function (
    PdkOrderCollectionFactory $orderFactory,
    int                       $expectedNumberOfShipments
) {
    $orders = new Collection($orderFactory->make());

    $orderFactory->store();

    $carriers = $orders
        ->pluck('deliveryOptions.carrier.carrier')
        ->toArray();

    factory(Settings::class)
        ->withCarriers($carriers)
        ->store();

    MockApi::enqueue(new ExamplePostShipmentsResponse());

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => $orders
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    $lastRequest      = MockApi::ensureLastRequest();
    $requestBody      = json_decode($lastRequest->getBody()->getContents(), true);
    $requestShipments = $requestBody['data']['shipments'];
    $labelAmount      = $orders->first()->deliveryOptions->labelAmount;

    if ($expectedNumberOfShipments === 1) {
        // Real multicollo: one top-level shipment carrying extra labels as secondary shipments
        expect($requestShipments)->toHaveLength(1)
            ->and($requestShipments[0])->toHaveKey('secondary_shipments')
            ->and($requestShipments[0]['secondary_shipments'])->toHaveLength($labelAmount - 1);
    } else {
        // Fake multicollo: each label results in a separate top-level shipment
        expect($requestShipments)->toHaveLength($expectedNumberOfShipments)
            ->and(array_key_exists('secondary_shipments', $requestShipments[0]))->toBeFalse();
    }

    $content = json_decode($response->getContent(), true);

    $responseOrders    = $content['data']['orders'];
    $responseShipments = Arr::pluck($responseOrders, 'shipments');

    $errors = Notifications::all()
        ->filter(function (Notification $notification) {
            return $notification->variant === Notification::VARIANT_ERROR;
        });

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($responseOrders)
        ->toHaveLength(count($orders))
        ->and($response->getStatusCode())
        ->toBe(200)
        // Expect no errors to have been added to notifications
        ->and($errors->toArrayWithoutNull())
        ->toBe([])
        ->and($responseShipments)->each->toHaveLength($expectedNumberOfShipments)
        ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
})
    ->with('multicolloPdkOrders');

it('adds api errors as notifications if shipment export fails', function () {
    $errorResponse = new ExamplePostShipmentsValidationErrorResponse();
    MockApi::enqueue($errorResponse);

    factory(CarrierSettings::class, RefCapabilitiesSharedCarrierV2::POSTNL)->store();
    factory(PdkOrder::class)
        ->withExternalIdentifier('error')
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
        )
        ->store();

    try {
        $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, ['orderIds' => 'error']);
        expect($response->getStatusCode())->toBe(400);
    } catch (ApiException $e) {
        $expectedErrorContent = $errorResponse->getContent();
        expect($e->getMessage())->toBe(
            'Request failed. Status code: ' . $expectedErrorContent['status_code'] . '. Message: Shipment validation error (request_id: ' . $expectedErrorContent['request_id'] . ')'
        );
        $notifications = Notifications::all()
            ->toArrayWithoutNull();

        $notification = Arr::first($notifications);

        expect($notifications)
            ->toHaveLength(1)
            ->and($notification)
            ->toHaveKeysAndValues([
                'title'    => 'Could not create shipment',
                'content'  => [
                    'data.shipments[0].options.return shipment option not supported',
                ],
                'variant'  => Notification::VARIANT_ERROR,
                'category' => Notification::CATEGORY_ACTION,
                'timeout'  => false,
                'tags'     => [
                    'action'   => PdkBackendActions::EXPORT_ORDERS,
                    'orderIds' => 'error',
                    'request_id' => $expectedErrorContent['request_id'],
                    'errors' => $expectedErrorContent['errors']
                ],
            ]);
    }
});

it('exports order and directly returns barcode if concept shipments is off', function () {
    factory(Settings::class)
        ->withOrder(factory(OrderSettings::class)->withConceptShipments(false))
        ->withCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->store();

    $collection = factory(PdkOrderCollection::class, 1)
        ->store()
        ->make();

    MockApi::enqueue(
        new ExamplePostShipmentsResponse(),
        new ExampleGetShipmentLabelsLinkV2Response(),
        new ExampleGetShipmentsResponse()
    );

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => Arr::pluck($collection->toArray(), 'externalIdentifier'),
    ]);

    $content = json_decode($response->getContent(), true);

    $responseOrders    = $content['data']['orders'];
    $responseShipments = Arr::pluck($responseOrders, 'shipments');

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($responseOrders)
        ->toHaveLength(count($responseOrders))
        ->and($response->getStatusCode())
        ->toBe(200)
        ->and($responseShipments)->each->toHaveLength(1)
        ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
});

it(
    'exports pickup order without signature',
    function (?RetailLocationFactory $pickupLocation, ShippingAddressFactory $shippingAddress) {
        factory(CarrierSettings::class)
            ->withId((string) RefTypesCarrier::POSTNL)
            ->withExportSignature(false)
            ->store();

        $orderWithPickup = factory(PdkOrder::class)
            ->withOrderDate('2020-01-01T00:00:00+00:00')
            ->withDeliveryOptionsWithPickupLocation($pickupLocation)
            ->withShippingAddress($shippingAddress)
            ->store()
            ->make();

        $collection = factory(PdkOrderCollection::class)
            ->push($orderWithPickup)
            ->store()
            ->make();

        MockApi::enqueue(new ExamplePostShipmentsResponse());

        $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
            'orderIds' => Arr::pluck($collection->toArray(), 'externalIdentifier'),
        ]);

        $content = json_decode($response->getContent(), true);

        $responseOrders    = $content['data']['orders'];
        $responseShipments = Arr::pluck($responseOrders, 'shipments');

        expect($response)
            ->toBeInstanceOf(Response::class)
            ->and($responseOrders)
            ->toHaveLength(count($responseOrders))
            ->and($response->getStatusCode())
            ->toBe(200)
            ->and($responseShipments)->each->toHaveLength(1)
            ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
    }
)
    ->with(
        [
            'dutch shipping location'   => [
                function () {
                    return factory(RetailLocation::class)->inTheNetherlands();
                },
                function () {
                    return factory(ShippingAddress::class)->inTheNetherlands();
                },
            ],
            'foreign shipping location' => [
                null,
                function () {
                    return factory(ShippingAddress::class)->inTheUnitedKingdom();
                },
            ],
        ]
    );

it(
    'exports evening order',
    function (ShippingAddressFactory $shippingAddress) {
        factory(CarrierSettings::class)
            ->withId((string) RefTypesCarrier::POSTNL)
            ->store();

        $order = factory(PdkOrder::class)
            ->withOrderDate('2020-01-01T00:00:00+00:00')
            ->withDeliveryOptions(
                factory(DeliveryOptions::class)
                    ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
            )
            ->withShippingAddress($shippingAddress)
            ->store()
            ->make();

        $collection = factory(PdkOrderCollection::class)
            ->push($order)
            ->store()
            ->make();

        MockApi::enqueue(new ExamplePostShipmentsResponse());

        $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
            'orderIds' => Arr::pluck($collection->toArray(), 'externalIdentifier'),
        ]);

        $content = json_decode($response->getContent(), true);

        $responseOrders    = $content['data']['orders'];
        $responseShipments = Arr::pluck($responseOrders, 'shipments');

        expect($response)
            ->toBeInstanceOf(Response::class)
            ->and($responseOrders)
            ->toHaveLength(count($responseOrders))
            ->and($response->getStatusCode())
            ->toBe(200)
            ->and($responseShipments)->each->toHaveLength(1)
            ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
    }
)
    ->with(
        [
            'foreign shipping location' =>
            function () {
                return factory(ShippingAddress::class)->inTheUnitedKingdom();
            },
        ]
    );

it(
    'exports international orders',
    function (
        PdkOrderCollectionFactory $factory,
        bool                      $accountHasCarrierSmallPackageContract,
        bool                      $carrierHasInternationalMailboxAllowed,
        callable                  $assertions
    ) {
        MockApi::enqueue(new ExamplePostShipmentsResponse());

        $collection  = $factory
            ->store()
            ->make();
        $fakeCarrier = $collection->first()->deliveryOptions->carrier;

        factory(CarrierSettings::class, $fakeCarrier->carrier)
            ->withAllowInternationalMailbox($carrierHasInternationalMailboxAllowed)
            ->store();

        factory(OrderSettings::class)
            ->withOrderMode($orderMode)
            ->withConceptShipments(true)
            ->store();

        factory(AccountGeneralSettings::class)
            ->withHasCarrierSmallPackageContract($accountHasCarrierSmallPackageContract)
            ->store();

        $orderIds = $collection->pluck('externalIdentifier');

        Actions::execute(PdkBackendActions::EXPORT_ORDERS, ['orderIds' => $orderIds->toArray()]);

        $lastRequest = MockApi::ensureLastRequest();
        $requestBody = json_decode($lastRequest->getBody()->getContents(), true);
        $assertions($requestBody['data']['shipments'][0]);
    }
)
    ->with([
        'without customs declaration' => [
            function () {
                return factory(PdkOrderCollection::class)->push(factory(PdkOrder::class)->toTheUnitedStates());
            },
            'accountHasCarrierSmallPackageContract' => false,
            'carrierHasInternationalMailboxAllowed' => false,
            // No explicit customs declaration — the system should auto-generate one for international shipments
            'assertions'                            => function () {
                return function (array $shipment) {
                    expect($shipment)->toHaveKey('customs_declaration')
                        ->and($shipment['customs_declaration']['contents'])->toBe(1);
                };
            },
        ],

        'with customs declaration (deprecated)' => [
            function () {
                return factory(PdkOrderCollection::class)->push(
                    factory(PdkOrder::class)
                        ->toTheUnitedStates()
                        ->withCustomsDeclaration(
                            factory(CustomsDeclaration::class)
                                ->withContents(3)
                                ->withWeight(3000)
                                ->withItems(
                                    factory(CustomsDeclarationItemCollection::class)->push(
                                        factory(CustomsDeclarationItem::class)
                                            ->withWeight(400)
                                            ->withAmount(3)
                                            ->withItemValue(1000)
                                            ->withDescription('hello')
                                            ->withCountry('DE')
                                            ->withClassification('123456')
                                    )
                                )
                        )
                );
            },
            'accountHasCarrierSmallPackageContract' => false,
            'carrierHasInternationalMailboxAllowed' => false,
            // Explicitly set customs declaration values should be forwarded as-is
            'assertions'                            => function () {
                return function (array $shipment) {
                    expect($shipment)->toHaveKey('customs_declaration')
                        ->and($shipment['customs_declaration']['contents'])->toBe(3)
                        ->and($shipment['customs_declaration']['items'])->toHaveLength(1)
                        ->and($shipment['customs_declaration']['items'][0]['description'])->toBe('hello')
                        ->and($shipment['customs_declaration']['items'][0]['weight'])->toBe(400)
                        ->and($shipment['customs_declaration']['items'][0]['amount'])->toBe(3);
                };
            },
        ],

        'with small package contract to Germany' => [
            function () {
                return factory(PdkOrderCollection::class)->push(
                    factory(PdkOrder::class)
                        ->toGermany()
                        ->withDeliveryOptions(
                            factory(DeliveryOptions::class)
                                ->withCarrier(
                                    factory(Carrier::class)
                                        ->fromPostNL()
                                        ->withPackageTypes([RefShipmentPackageTypeV2::MAILBOX])
                                )
                                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
                        )
                );
            },
            'accountHasCarrierSmallPackageContract' => true,
            'carrierHasInternationalMailboxAllowed' => true,
            // Carrier supports mailbox internationally and account has the contract — package type should be mailbox
            'assertions'                            => function () {
                return function (array $shipment) {
                    expect($shipment['options']['package_type'])->toBe(DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID);
                };
            },
        ],

        'mailbox filtered when account lacks small package contract' => [
            function () {
                return factory(PdkOrderCollection::class)->push(
                    factory(PdkOrder::class)
                        ->toGermany()
                        ->withDeliveryOptions(
                            factory(DeliveryOptions::class)
                                ->withCarrier(
                                    factory(Carrier::class)
                                        ->fromPostNL()
                                        ->withPackageTypes([RefShipmentPackageTypeV2::MAILBOX])
                                )
                                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
                        )
                );
            },
            'accountHasCarrierSmallPackageContract' => false,
            'carrierHasInternationalMailboxAllowed' => true,
            // Carrier supports mailbox and settings allow it, but account has no small package contract — mailbox must be filtered out
            'assertions'                            => function () {
                return function (array $shipment) {
                    expect($shipment['options']['package_type'])->not->toBe(DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID);
                };
            },
        ],
    ]);

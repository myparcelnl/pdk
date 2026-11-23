<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\Account\Service\PdkAccountFeaturesService;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrdersResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleCapabilitiesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesSettingsMock;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ShipmentDefsIncotermsRule;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance(),
    new UsesAccountMock(),
    new UsesApiMock(),
    new UsesSdkApiMock(),
    new UsesNotificationsMock(),
    new UsesSettingsMock()
);

it('exports the default incoterms rule from the capabilities when the order has no rule', function (bool $orderMode) {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    TestBootstrapper::hasSubscriptionFeatures(
        $orderMode ? [PdkAccountFeaturesService::FEATURE_LEGACY_ORDER_MANAGEMENT] : []
    );

    // Concept shipments stop the export after the create call, so the last request is the one under test.
    factory(Settings::class)
        ->withOrder(factory(OrderSettings::class)->withConceptShipments(true))
        ->store();

    $capability = [
        'carrier'            => $carrier,
        'contract'           => ['id' => 100, 'type' => 'MAIN'],
        'packageTypes'       => ['PACKAGE'],
        'options'            => (object) [],
        'physicalProperties' => [
            'weight' => [
                'min' => ['value' => 0, 'unit' => 'g'],
                'max' => ['value' => 30000, 'unit' => 'g'],
            ],
        ],
        'deliveryTypes'      => ['STANDARD_DELIVERY'],
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
        'incoterms'          => [
            'rule' => [
                'default' => ShipmentDefsIncotermsRule::DAP,
                'values'  => [ShipmentDefsIncotermsRule::DAP, ShipmentDefsIncotermsRule::DDP],
            ],
        ],
    ];

    // One response per distinct capabilities request in the calculation; the rest is served from cache.
    for ($i = 0; $i < 5; $i++) {
        MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([$capability]));
    }

    $collection = factory(PdkOrderCollection::class)
        ->push(
            factory(PdkOrder::class)
                ->withShippingAddress(factory(ShippingAddress::class)->inTheUnitedStates())
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withCarrier(factory(Carrier::class)->withCarrier($carrier)->withAllCapabilities())
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

    $body    = json_decode(MockApi::ensureLastRequest()->getBody()->getContents(), true);
    $options = $orderMode
        ? $body['data']['orders'][0]['shipment']['options']
        : $body['data']['shipments'][0]['options'];

    expect($options['incoterms'] ?? null)->toBe(['rule' => ShipmentDefsIncotermsRule::DAP]);
})->with([
    'shipment mode' => [false],
    'order mode'    => [true],
]);

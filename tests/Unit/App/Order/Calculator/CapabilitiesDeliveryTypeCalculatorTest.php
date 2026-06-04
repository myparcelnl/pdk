<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\App\Order\Calculator;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Order\Calculator\General\CapabilitiesDeliveryTypeCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleCapabilitiesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock(), new UsesSdkApiMock());

/**
 * Build a single capabilities result entry advertising the given supported delivery types.
 */
function deliveryTypeCapabilityResult(string $carrier, array $deliveryTypes): array
{
    return [
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
        'deliveryTypes'      => $deliveryTypes,
        'transactionTypes'   => [],
        'collo'              => ['max' => 1],
    ];
}

it('keeps delivery type when it is supported by the carrier capability', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesDeliveryTypeCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier($carrier)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        deliveryTypeCapabilityResult($carrier, [RefTypesDeliveryTypeV2::STANDARD, RefTypesDeliveryTypeV2::MORNING]),
    ]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME)
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_MORNING_NAME)
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->deliveryType)->toBe(DeliveryOptions::DELIVERY_TYPE_MORNING_NAME);

    $reset();
});

it('resets to standard when current delivery type is not supported', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesDeliveryTypeCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier($carrier)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    // Carrier supports only STANDARD; order asks for EVENING → reset to STANDARD.
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        deliveryTypeCapabilityResult($carrier, [RefTypesDeliveryTypeV2::STANDARD]),
    ]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME)
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->deliveryType)->toBe(DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME);

    $reset();
});

it('falls back to first available type when standard is not supported', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesDeliveryTypeCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier($carrier)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    // Carrier supports only PICKUP; order asks for EVENING → no STANDARD, pick first listed (PICKUP).
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        deliveryTypeCapabilityResult($carrier, [RefTypesDeliveryTypeV2::PICKUP]),
    ]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME)
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->deliveryType)->toBe(DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME);

    $reset();
});

it('resets to standard when carrier capability is missing entirely', function () {
    $carrier = RefCapabilitiesSharedCarrierV2::POSTNL;

    $reset = mockPdkProperty('orderCalculators', [CapabilitiesDeliveryTypeCalculator::class]);

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)
                ->push(factory(Carrier::class)
                    ->withCarrier($carrier)
                    ->withCapabilityPackageTypes(['PACKAGE']))
        )
        ->store();

    factory(Settings::class)
        ->withCarrier($carrier)
        ->store();

    // Carrier returns no capability for this combination → safe default to STANDARD.
    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([]));

    $order = factory(PdkOrder::class)
        ->withShippingAddress(factory(ShippingAddress::class)->withCc('NL'))
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME)
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->deliveryType)->toBe(DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME);

    $reset();
});

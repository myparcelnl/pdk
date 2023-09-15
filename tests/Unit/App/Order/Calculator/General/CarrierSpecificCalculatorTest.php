<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Calculator\General\CarrierSpecificCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptionsFactory;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

function doTest(string $carrier, ShipmentOptionsFactory $shipmentOptions, array $result): void
{
    $reset = mockPdkProperty('orderCalculators', [CarrierSpecificCalculator::class]);

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions($shipmentOptions)
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    $options = $newOrder->deliveryOptions->shipmentOptions->only(
        [ShipmentOptions::AGE_CHECK, ShipmentOptions::SIGNATURE, ShipmentOptions::ONLY_RECIPIENT]
    );

    expect($options)->toEqual($result);

    $reset();
}

it('calculates age check for postnl', function (ShipmentOptionsFactory $shipmentOptions, array $result) {
    doTest(Carrier::CARRIER_POSTNL_NAME, $shipmentOptions, $result);
})->with([
    'age check enabled should enable signature and age check' => [
        fn() => factory(ShipmentOptions::class)
            ->withAgeCheck(TriStateService::ENABLED)
            ->withSignature(TriStateService::DISABLED)
            ->withOnlyRecipient(TriStateService::DISABLED),
        [
            ShipmentOptions::AGE_CHECK      => TriStateService::ENABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::ENABLED,
        ],
    ],

    'age check disabled should not disable signature and age check' => [
        fn() => factory(ShipmentOptions::class)
            ->withAgeCheck(TriStateService::DISABLED)
            ->withSignature(TriStateService::ENABLED)
            ->withOnlyRecipient(TriStateService::ENABLED),
        [
            ShipmentOptions::AGE_CHECK      => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::ENABLED,
        ],
    ],
]);

it('calculates age check for dhlforyou', function (ShipmentOptionsFactory $shipmentOptions, array $result) {
    doTest(Carrier::CARRIER_DHL_FOR_YOU_NAME, $shipmentOptions, $result);
})->with([
    'when age check and only recipient are enabled, age check wins' => [
        fn() => factory(ShipmentOptions::class)
            ->withAgeCheck(TriStateService::ENABLED)
            ->withOnlyRecipient(TriStateService::ENABLED),
        [
            ShipmentOptions::AGE_CHECK      => TriStateService::ENABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::INHERIT,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
        ],
    ],

    'when both are disabled, nothing happens' => [
        fn() => factory(ShipmentOptions::class)
            ->withAgeCheck(TriStateService::DISABLED)
            ->withOnlyRecipient(TriStateService::DISABLED),
        [
            ShipmentOptions::AGE_CHECK      => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::INHERIT,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
        ],
    ],
]);

it('should do nothing for other carriers', function (string $carrierName) {
    doTest(
        $carrierName,
        factory(ShipmentOptions::class)
            ->withAgeCheck(TriStateService::ENABLED)
            ->withSignature(TriStateService::ENABLED)
            ->withOnlyRecipient(TriStateService::ENABLED),
        [
            ShipmentOptions::AGE_CHECK      => TriStateService::ENABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::ENABLED,
        ]
    );
})->with([
    Carrier::CARRIER_BPOST_NAME,
    Carrier::CARRIER_DHL_EUROPLUS_NAME,
    Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
    Carrier::CARRIER_DPD_NAME,
]);

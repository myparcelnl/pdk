<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator;

use MyParcelNL\Pdk\App\Order\Calculator\General\CarrierSpecificCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderFactory;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
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

function doTest(
    string                  $carrier,
    ?callable               $orderCallback,
    ?ShipmentOptionsFactory $shipmentOptions,
    array                   $result
): void {
    $reset = mockPdkProperty('orderCalculators', [CarrierSpecificCalculator::class]);

    $shipmentOptionsFactory = $shipmentOptions ?? factory(ShipmentOptions::class);

    $orderFactory = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions($shipmentOptionsFactory)
        );

    if ($orderCallback) {
        $orderFactory = $orderCallback($orderFactory);
    }

    $order = $orderFactory->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    $options = $newOrder->deliveryOptions->shipmentOptions->toArray();

    expect($options)->toHaveKeysAndValues($result);

    $reset();
}

it('calculates options for postnl', function (ShipmentOptionsFactory $shipmentOptions, array $result) {
    doTest(Carrier::CARRIER_POSTNL_NAME, null, $shipmentOptions, $result);
})->with([
    'age check enabled should enable signature and age check' => [
        function () {
            return factory(ShipmentOptions::class)
                ->withAgeCheck(TriStateService::ENABLED)
                ->withSignature(TriStateService::DISABLED)
                ->withOnlyRecipient(TriStateService::DISABLED);
        },
        [
            ShipmentOptions::AGE_CHECK      => TriStateService::ENABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::ENABLED,
        ],
    ],

    'age check disabled should not disable signature and age check' => [
        function () {
            return factory(ShipmentOptions::class)
                ->withAgeCheck(TriStateService::DISABLED)
                ->withSignature(TriStateService::ENABLED)
                ->withOnlyRecipient(TriStateService::ENABLED);
        },
        [
            ShipmentOptions::AGE_CHECK      => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::ENABLED,
        ],
    ],
]);

it('calculates options for dhlforyou', function (
    ?callable              $orderCallback,
    ShipmentOptionsFactory $shipmentOptions,
    array                  $result
) {
    doTest(Carrier::CARRIER_DHL_FOR_YOU_NAME, $orderCallback, $shipmentOptions, $result);
})->with([
    'when age check and only recipient are enabled, age check wins' => [
        null,
        function () {
            return factory(ShipmentOptions::class)
                ->withAgeCheck(TriStateService::ENABLED)
                ->withOnlyRecipient(TriStateService::ENABLED);
        },
        [
            ShipmentOptions::AGE_CHECK      => TriStateService::ENABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::INHERIT,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
        ],
    ],

    'when age check and only recipient both are disabled, nothing happens' => [
        null,
        function () {
            return factory(ShipmentOptions::class)
                ->withAgeCheck(TriStateService::DISABLED)
                ->withOnlyRecipient(TriStateService::DISABLED);
        },
        [
            ShipmentOptions::AGE_CHECK      => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::INHERIT,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
        ],
    ],

    'when country is not local, age check, only recipient and same day delivery are turned off' => [
        function () {
            return function (PdkOrderFactory $orderFactory) {
                return $orderFactory->withShippingAddress(factory(ShippingAddress::class)->inFrance());
            };
        },
        function () {
            return factory(ShipmentOptions::class)
                ->withAgeCheck(TriStateService::ENABLED)
                ->withSameDayDelivery(TriStateService::ENABLED)
                ->withOnlyRecipient(TriStateService::ENABLED);
        },
        [
            ShipmentOptions::AGE_CHECK         => TriStateService::DISABLED,
            ShipmentOptions::ONLY_RECIPIENT    => TriStateService::DISABLED,
            ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE         => TriStateService::INHERIT,
        ],
    ],
]);

it('calculates options for dhl europlus', function (
    ?callable              $orderCallback,
    ShipmentOptionsFactory $shipmentOptions,
    array                  $result
) {
    doTest(Carrier::CARRIER_DHL_EUROPLUS_NAME, $orderCallback, $shipmentOptions, $result);
})->with([
    'when signature is disabled, enable it anyway' => [
        null,
        function () {
            return factory(ShipmentOptions::class)
                ->withSignature(TriStateService::DISABLED);
        },
        [
            ShipmentOptions::SIGNATURE => TriStateService::ENABLED,
        ],
    ],
]);

it('calculates options for dhl parcel connect', function (
    ?callable              $orderCallback,
    ShipmentOptionsFactory $shipmentOptions,
    array                  $result
) {
    doTest(Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME, $orderCallback, $shipmentOptions, $result);
})->with([
    'when signature is disabled, enable it anyway' => [
        null,
        function () {
            return factory(ShipmentOptions::class)
                ->withSignature(TriStateService::DISABLED);
        },
        [
            ShipmentOptions::SIGNATURE => TriStateService::ENABLED,
        ],
    ],
]);

it('removes delivery date for dpd', function (string $date, ?string $expected) {
    $reset = mockPdkProperty('orderCalculators', [CarrierSpecificCalculator::class]);

    $orderFactory = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withDate($date)
                ->withCarrier(Carrier::CARRIER_DPD_NAME)
        );

    $order = $orderFactory->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->date)->toBe($expected);

    $reset();
})->with([
    'real date' => [
        (new \DateTimeImmutable('tomorrow'))->format('Y-m-d'),
        null,
    ],
    'past date' => [
        '2022-01-01',
        null,
    ],
]);

it('should do nothing for other carriers', function (string $carrierName) {
    $result = [
        ShipmentOptions::AGE_CHECK      => TriStateService::ENABLED,
        ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
        ShipmentOptions::ONLY_RECIPIENT => TriStateService::ENABLED,
    ];

    doTest($carrierName, null, factory(ShipmentOptions::class)->withAllOptions(), $result);
})->with([
    Carrier::CARRIER_BPOST_NAME,
]);

it('should disable return and only recipient when pickup is enabled', function () {
    $result = [
        ShipmentOptions::DIRECT_RETURN  => TriStateService::DISABLED,
        ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
        ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
    ];

    doTest(Carrier::CARRIER_POSTNL_NAME, function () {
        return factory(PdkOrder::class)
            ->withDeliveryOptions(
                factory(DeliveryOptions::class)
                    ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME)
            );
    }, factory(ShipmentOptions::class), $result);
});

it(
    'should enable tracked for postnl small package order outside of the netherlands',
    function (string $countryCode, bool $expected) {
        $orderFactory = factory(PdkOrder::class)
            ->withShippingAddress(factory(ShippingAddress::class)->withCc($countryCode))
            ->withDeliveryOptions(
                factory(DeliveryOptions::class)
                    ->withCarrier(Carrier::CARRIER_POSTNL_NAME)
                    ->withPackageType(DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME)
            );

        $order = $orderFactory->make();

        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
        $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
        $newOrder = $service->calculate($order);

        $options = $newOrder->deliveryOptions->shipmentOptions->toArray();

        expect($options)->toHaveKeysAndValues([
            ShipmentOptions::TRACKED => $expected ? TriStateService::ENABLED : TriStateService::DISABLED,
        ]);
    }
)->with([['cc' => 'NL', 'expected' => false], ['cc' => 'BE', 'expected' => true], ['cc' => 'DE', 'expected' => true]]);

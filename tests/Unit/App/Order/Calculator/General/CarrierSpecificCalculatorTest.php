<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator;

use DateTimeImmutable;
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
    string    $carrier,
    ?callable $orderCb,
    ?callable $shipmentOptionsCb,
    array     $expected
): void {
    $reset = mockPdkProperty('orderCalculators', [CarrierSpecificCalculator::class]);

    $shipmentOptionsFactory = factory(ShipmentOptions::class);

    if ($shipmentOptionsCb) {
        $shipmentOptionsFactory = $shipmentOptionsCb($shipmentOptionsFactory);
    }

    $orderFactory = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions($shipmentOptionsFactory)
        );

    if ($orderCb) {
        $orderFactory = $orderCb($orderFactory);
    }

    $order = $orderFactory->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    $options = $newOrder->deliveryOptions->shipmentOptions->toArray();

    expect($options)->toHaveKeysAndValues($expected);

    $reset();
}

it('calculates options for $carrier', function (
    string    $carrier,
    ?callable $orderCb,
    ?callable $shipmentOptionsCb,
    array     $expected
) {
    doTest($carrier, $orderCb, $shipmentOptionsCb, $expected);
})->with([
    /**
     * PostNL
     */

    'postnl: age check enabled enables signature and only recipient' => [
        'carrier'           => Carrier::CARRIER_POSTNL_NAME,
        'orderCb'           => null,
        'shipmentOptionsCb' => function () {
            return function (ShipmentOptionsFactory $factory) {
                return $factory
                    ->withAgeCheck(TriStateService::ENABLED)
                    ->withSignature(TriStateService::DISABLED)
                    ->withOnlyRecipient(TriStateService::DISABLED);
            };
        },
        'expected'          => [
            ShipmentOptions::AGE_CHECK      => TriStateService::ENABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::ENABLED,
        ],
    ],

    'postnl: age check disabled does not disable signature and only recipient' => [
        'carrier'           => Carrier::CARRIER_POSTNL_NAME,
        'orderCb'           => null,
        'shipmentOptionsCb' => function () {
            return function (ShipmentOptionsFactory $factory) {
                return $factory
                    ->withAgeCheck(TriStateService::DISABLED)
                    ->withSignature(TriStateService::ENABLED)
                    ->withOnlyRecipient(TriStateService::ENABLED);
            };
        },
        'expected'          => [
            ShipmentOptions::AGE_CHECK      => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::ENABLED,
        ],
    ],

    'postnl: disables return and only recipient when pickup is enabled' => [
        'carrier'           => Carrier::CARRIER_POSTNL_NAME,
        'orderCb'           => function () {
            return function (PdkOrderFactory $factory) {
                $deliveryOptions = factory(DeliveryOptions::class)
                    ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME);

                return $factory->withDeliveryOptions($deliveryOptions);
            };
        },
        'shipmentOptionsCb' => null,
        'expected'          => [
            ShipmentOptions::DIRECT_RETURN  => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
        ],
    ],

    /**
     * DHL For You
     */

    'dhlforyou: when age check and only recipient are enabled, age check wins' => [
        'carrier'           => Carrier::CARRIER_DHL_FOR_YOU_NAME,
        'orderCb'           => null,
        'shipmentOptionsCb' => function () {
            return function (ShipmentOptionsFactory $factory) {
                return $factory
                    ->withAgeCheck(TriStateService::ENABLED)
                    ->withOnlyRecipient(TriStateService::ENABLED);
            };
        },
        'expected'          => [
            ShipmentOptions::AGE_CHECK      => TriStateService::ENABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::INHERIT,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
        ],
    ],

    'dhlforyou: when age check and only recipient both are disabled, nothing happens' => [
        'carrier'           => Carrier::CARRIER_DHL_FOR_YOU_NAME,
        'orderCb'           => null,
        'shipmentOptionsCb' => function () {
            return function (ShipmentOptionsFactory $factory) {
                return $factory
                    ->withAgeCheck(TriStateService::DISABLED)
                    ->withOnlyRecipient(TriStateService::DISABLED);
            };
        },
        'expected'          => [
            ShipmentOptions::AGE_CHECK      => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::INHERIT,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
        ],
    ],

    'dhlforyou: when country is not local, age check, only recipient and same day delivery are turned off' => [
        'carrier'           => Carrier::CARRIER_DHL_FOR_YOU_NAME,
        'orderCb'           => function () {
            return function (PdkOrderFactory $factory) {
                return $factory->withShippingAddress(factory(ShippingAddress::class)->inFrance());
            };
        },
        'shipmentOptionsCb' => function () {
            return function (ShipmentOptionsFactory $factory) {
                return $factory
                    ->withAgeCheck(TriStateService::ENABLED)
                    ->withSameDayDelivery(TriStateService::ENABLED)
                    ->withOnlyRecipient(TriStateService::ENABLED);
            };
        },
        'expected'          => [
            ShipmentOptions::AGE_CHECK         => TriStateService::DISABLED,
            ShipmentOptions::ONLY_RECIPIENT    => TriStateService::DISABLED,
            ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE         => TriStateService::INHERIT,
        ],
    ],

    /**
     * DHL Europlus
     */

    'dhleuroplus: when signature is disabled, enable it anyway' => [
        'carrier'           => Carrier::CARRIER_DHL_EUROPLUS_NAME,
        'orderCb'           => null,
        'shipmentOptionsCb' => function () {
            return function (ShipmentOptionsFactory $factory) {
                return $factory->withSignature(TriStateService::DISABLED);
            };
        },
        'expected'          => [ShipmentOptions::SIGNATURE => TriStateService::ENABLED],
    ],

    /**
     * DHL Parcel Connect
     */

    'dhlparcelconnect: when signature is disabled, enable it anyway' => [
        'carrier'           => Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
        'orderCb'           => null,
        'shipmentOptionsCb' => function () {
            return function (ShipmentOptionsFactory $factory) {
                return $factory
                    ->withSignature(TriStateService::DISABLED);
            };
        },
        'expected'          => [ShipmentOptions::SIGNATURE => TriStateService::ENABLED],
    ],
]);

it('should do nothing for other carriers', function (string $carrierName) {
    doTest(
        $carrierName,
        null,
        function (ShipmentOptionsFactory $factory) {
            return $factory->withAllOptions();
        },
        [
            ShipmentOptions::AGE_CHECK      => TriStateService::ENABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::ENABLED,
        ]
    );
})->with([
    Carrier::CARRIER_BPOST_NAME,
    Carrier::CARRIER_DPD_NAME,
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
    'real date' => [(new DateTimeImmutable('tomorrow'))->format('Y-m-d'), null],
    'past date' => ['2022-01-01', null],
]);

it('enables tracked for postnl small package order outside of nl', function (string $countryCode, bool $expected) {
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
})->with([
    ['cc' => 'NL', 'expected' => false],
    ['cc' => 'BE', 'expected' => true],
    ['cc' => 'DE', 'expected' => true],
]);

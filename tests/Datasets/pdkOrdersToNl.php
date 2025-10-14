<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

use function MyParcelNL\Pdk\Tests\factory;

dataset('pdk orders domestic', [
    'single order' => function () {
        return factory(PdkOrderCollection::class)->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions([
                    'shipmentOptions' => [
                        'signature' => TriStateService::ENABLED,
                    ],
                ])
                ->withNotes([
                    factory(PdkOrderNote::class)->withApiIdentifier('90001'),
                    factory(PdkOrderNote::class)
                        ->byCustomer()
                        ->withNote('hello'),
                ])
        );
    },

    'carrier dhl for you' => function () {
        return factory(PdkOrderCollection::class)->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)->withCarrier(
                        sprintf('%s:1234', Carrier::CARRIER_DHL_FOR_YOU_LEGACY_NAME)
                    )
                )
        );
    },

    'various delivery options' => function () {
        return factory(PdkOrderCollection::class)->push(
            factory(PdkOrder::class)
                ->withLines(factory(PdkOrderLineCollection::class, 1)->eachWith(['quantity' => 5]))
                ->withDeliveryOptions([
                    'carrier'     => Carrier::CARRIER_POSTNL_LEGACY_NAME,
                    'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                ]),
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
                        ->withDate('2077-10-23 09:47:51')
                        ->withShipmentOptions(
                            factory(ShipmentOptions::class)
                                ->withAgeCheck(TriStateService::DISABLED)
                                ->withOnlyRecipient(TriStateService::ENABLED)
                                ->withSignature(TriStateService::ENABLED)
                        )
                ),
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withCarrier(Carrier::CARRIER_DHL_FOR_YOU_LEGACY_NAME)
                        ->withShipmentOptions(
                            factory(ShipmentOptions::class)
                                ->withAgeCheck(TriStateService::ENABLED)
                                ->withHideSender(TriStateService::ENABLED)
                                ->withInsurance(TriStateService::ENABLED)
                                ->withOnlyRecipient(TriStateService::ENABLED)
                                ->withSignature(TriStateService::ENABLED)
                        )
                )
        );
    },

    'carrier UPS to Belgium' => function () {
        return factory(PdkOrderCollection::class)->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withCarrier(Carrier::CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME)
                        ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EXPRESS_NAME)
                )
                ->toBelgium()
        );
    },

    'carrier UPS to Netherlands express' => function () {
        return factory(PdkOrderCollection::class)->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withCarrier(Carrier::CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME)
                        ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EXPRESS_NAME)
                )
                ->toTheNetherlands()
        );
    },

    'carrier GLS to Netherlands' => function () {
        return factory(PdkOrderCollection::class)->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withCarrier(
                            sprintf('%s:1234', Carrier::CARRIER_GLS_LEGACY_NAME)
                        )
                        ->withShipmentOptions(
                            factory(ShipmentOptions::class)
                                // For Netherlands: signature is default OFF
                                ->withSignature(TriStateService::DISABLED)
                                ->withOnlyRecipient(TriStateService::ENABLED)
                        )
                )
                ->toTheNetherlands()
        );
    },

    'carrier GLS to Germany' => function () {
        return factory(PdkOrderCollection::class)->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withCarrier(
                            sprintf('%s:1234', Carrier::CARRIER_GLS_LEGACY_NAME)
                        )
                        ->withShipmentOptions(
                            factory(ShipmentOptions::class)
                                // For EU (Germany): signature will be auto-enabled by calculator
                                ->withSignature(TriStateService::DISABLED)
                        )
                )
                ->toGermany()
        );
    },

    'carrier GLS with saturday delivery' => function () {
        return factory(PdkOrderCollection::class)->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withCarrier(
                            sprintf('%s:1234', Carrier::CARRIER_GLS_LEGACY_NAME)
                        )
                        ->withShipmentOptions(
                            factory(ShipmentOptions::class)
                                ->withSaturdayDelivery(TriStateService::ENABLED)
                                ->withSignature(TriStateService::ENABLED)
                        )
                )
                ->toTheNetherlands()
        );
    },

    'carrier GLS with insurance' => function () {
        return factory(PdkOrderCollection::class)->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withCarrier(
                            sprintf('%s:1234', Carrier::CARRIER_GLS_LEGACY_NAME)
                        )
                        ->withShipmentOptions(
                            factory(ShipmentOptions::class)
                                ->withInsurance(TriStateService::ENABLED)
                                ->withSignature(TriStateService::ENABLED)
                        )
                )
                ->toTheNetherlands()
        );
    },

    'carrier GLS custom contract' => function () {
        return factory(PdkOrderCollection::class)->push(
            factory(PdkOrder::class)
                ->withDeliveryOptions(
                    factory(DeliveryOptions::class)
                        ->withCarrier(
                            sprintf('%s:5678', Carrier::CARRIER_GLS_LEGACY_NAME)
                        )
                        ->withShipmentOptions(
                            factory(ShipmentOptions::class)
                                ->withOnlyRecipient(TriStateService::ENABLED)
                                ->withLargeFormat(TriStateService::ENABLED)
                        )
                )
                ->toTheNetherlands()
        );
    },
]);

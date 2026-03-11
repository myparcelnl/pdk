<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;

dataset('multicolloPdkOrders', [
    'real multicollo order' => [
        'factory'                   => function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withDeliveryOptions(
                        factory(DeliveryOptions::class)
                            ->withLabelAmount(5)
                            ->withCarrier(
                                factory(Carrier::class)
                                    ->withName(RefTypesCarrierV2::POSTNL)
                                    ->withOutboundFeatures([
                                        'metadata' => [
                                            'multiCollo' => true,
                                        ],
                                    ])
                            )
                    )
            );
        },
        'expectedNumberOfShipments' => 1,
    ],

    'fake multicollo' => [
        'factory'                   => function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withDeliveryOptions(
                        factory(DeliveryOptions::class)
                            ->withLabelAmount(2)
                            ->withCarrier(
                                factory(Carrier::class)
                                    ->withName(RefTypesCarrierV2::POSTNL)
                                    ->withOutboundFeatures([
                                        'metadata' => [
                                            'multiCollo' => false,
                                        ],
                                    ])
                            )
                    )
            );
        },
        'expectedNumberOfShipments' => 2,
    ],
]);

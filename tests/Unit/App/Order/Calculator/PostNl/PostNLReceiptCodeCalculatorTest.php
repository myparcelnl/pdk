<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\PostNl;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierMetadata;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('handles receipt code', function (array $input, array $expected, string $cc = 'NL') {
    $reset = mockPdkProperty('orderCalculators', [PostNLReceiptCodeCalculator::class]);

    $defaults = [
        ShipmentOptions::LABEL_DESCRIPTION => TriStateService::INHERIT,
        ShipmentOptions::INSURANCE         => TriStateService::INHERIT,
        ShipmentOptions::AGE_CHECK         => TriStateService::INHERIT,
        ShipmentOptions::DIRECT_RETURN     => TriStateService::INHERIT,
        ShipmentOptions::HIDE_SENDER       => TriStateService::INHERIT,
        ShipmentOptions::LARGE_FORMAT      => TriStateService::INHERIT,
        ShipmentOptions::ONLY_RECIPIENT    => TriStateService::INHERIT,
        ShipmentOptions::RECEIPT_CODE      => TriStateService::INHERIT,
        ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::INHERIT,
        ShipmentOptions::SIGNATURE         => TriStateService::INHERIT,
        ShipmentOptions::TRACKED           => TriStateService::INHERIT,
        ShipmentOptions::COLLECT           => TriStateService::INHERIT,
        ShipmentOptions::EXCLUDE_PARCEL_LOCKERS => TriStateService::INHERIT,
        ShipmentOptions::FRESH_FOOD        => TriStateService::INHERIT,
        ShipmentOptions::FROZEN            => TriStateService::INHERIT,
    ];

    $order = factory(PdkOrder::class)
        ->withShippingAddress(['cc' => $cc])
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier(
                    factory(Carrier::class)
                        ->withName(Carrier::CARRIER_POSTNL_NAME)
                        ->withOutboundFeatures(
                            factory(PropositionCarrierFeatures::class)
                                ->withShipmentOptions([PropositionCarrierFeatures::SHIPMENT_OPTION_INSURANCE_NAME])
                                ->withMetadata([PropositionCarrierMetadata::FEATURE_NAME_INSURANCE_OPTIONS => [5000, 10000, 25000]])
                        )
                )
                ->withShipmentOptions(factory(ShipmentOptions::class)->with(array_replace($defaults, $input)))
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->toArray())->toBe(array_replace($defaults, $expected));

    $reset();
})->with([
    'changes nothing when receipt code is disabled' => [
        [ShipmentOptions::RECEIPT_CODE => TriStateService::DISABLED],
        [ShipmentOptions::RECEIPT_CODE => TriStateService::DISABLED],
    ],

    'disables receipt code for NL when age check is enabled' => [
        [
            ShipmentOptions::AGE_CHECK    => TriStateService::ENABLED,
            ShipmentOptions::RECEIPT_CODE => TriStateService::ENABLED,
        ],
        [
            ShipmentOptions::AGE_CHECK    => TriStateService::ENABLED,
            ShipmentOptions::RECEIPT_CODE => TriStateService::DISABLED,
        ],
        'NL',
    ],

    'disables age check for BE when receipt code is enabled' => [
        [
            ShipmentOptions::AGE_CHECK    => TriStateService::ENABLED,
            ShipmentOptions::RECEIPT_CODE => TriStateService::ENABLED,
        ],
        [
            ShipmentOptions::AGE_CHECK      => TriStateService::DISABLED,
            ShipmentOptions::RECEIPT_CODE   => TriStateService::ENABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::DISABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
            ShipmentOptions::LARGE_FORMAT   => TriStateService::DISABLED,
            ShipmentOptions::DIRECT_RETURN  => TriStateService::DISABLED,
        ],
        'BE',
    ],

    'disables receipt code when shipping to a non-NL or BE country' => [
        [ShipmentOptions::RECEIPT_CODE => TriStateService::ENABLED],
        [ShipmentOptions::RECEIPT_CODE => TriStateService::DISABLED],
        'FR',
    ],

    'disables signature, only recipient, large format and return when receipt code is enabled' => [
        [
            ShipmentOptions::RECEIPT_CODE   => TriStateService::ENABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::ENABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::ENABLED,
            ShipmentOptions::LARGE_FORMAT   => TriStateService::ENABLED,
            ShipmentOptions::DIRECT_RETURN  => TriStateService::ENABLED,
        ],
        [
            ShipmentOptions::RECEIPT_CODE   => TriStateService::ENABLED,
            ShipmentOptions::SIGNATURE      => TriStateService::DISABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
            ShipmentOptions::LARGE_FORMAT   => TriStateService::DISABLED,
            ShipmentOptions::DIRECT_RETURN  => TriStateService::DISABLED,
        ],
    ],

    'does not change insurance when receipt code is enabled and insurance is already set' => [
        [
            ShipmentOptions::RECEIPT_CODE => TriStateService::ENABLED,
            ShipmentOptions::INSURANCE    => 10000,
        ],
        [
            ShipmentOptions::RECEIPT_CODE   => TriStateService::ENABLED,
            ShipmentOptions::INSURANCE      => 10000,
            ShipmentOptions::SIGNATURE      => TriStateService::DISABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
            ShipmentOptions::LARGE_FORMAT   => TriStateService::DISABLED,
            ShipmentOptions::DIRECT_RETURN  => TriStateService::DISABLED,
        ],
    ],

    'sets minimum insurance when receipt code is enabled and insurance is not set' => [
        [
            ShipmentOptions::RECEIPT_CODE => TriStateService::ENABLED,
            ShipmentOptions::INSURANCE    => TriStateService::DISABLED,
        ],
        [
            ShipmentOptions::RECEIPT_CODE   => TriStateService::ENABLED,
            ShipmentOptions::INSURANCE      => 5000,
            ShipmentOptions::SIGNATURE      => TriStateService::DISABLED,
            ShipmentOptions::ONLY_RECIPIENT => TriStateService::DISABLED,
            ShipmentOptions::LARGE_FORMAT   => TriStateService::DISABLED,
            ShipmentOptions::DIRECT_RETURN  => TriStateService::DISABLED,
        ],
    ],
]);

it('sets insurance to 0 when no valid insurance amounts are available', function () {
    $reset = mockPdkProperty('orderCalculators', [PostNLReceiptCodeCalculator::class]);

    $carrier = factory(Carrier::class)
        ->withName(Carrier::CARRIER_POSTNL_NAME)
        ->withOutboundFeatures(
            factory(PropositionCarrierFeatures::class)
                ->withShipmentOptions([PropositionCarrierFeatures::SHIPMENT_OPTION_INSURANCE_NAME])
                ->withMetadata([PropositionCarrierMetadata::FEATURE_NAME_INSURANCE_OPTIONS => [0]])
        )
        ->make();

    $order = factory(PdkOrder::class)
        ->toTheNetherlands()
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($carrier)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)
                        ->withReceiptCode(TriStateService::ENABLED)
                        ->withInsurance(TriStateService::DISABLED)
                )
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->insurance)->toBe(0);

    $reset();
});

<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

function setup(): void
{
    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)->push(
                factory(Carrier::class)->fromPostNL(),
                factory(Carrier::class)
                    ->fromDhlForYou()
                    ->withContractId(12345)
            )
        )
        ->store();

    factory(Settings::class)
        ->withCarrierPostNl(
            factory(CarrierSettings::class)
                ->withExportAgeCheck(true)
                ->withExportSignature(true)
        )
        ->withCarrier(
            'DHL_FOR_YOU:12345',
            factory(CarrierSettings::class)->withExportOnlyRecipient(true)
        )
        ->store();

    factory(PdkProduct::class)
        ->withExternalIdentifier('product-1')
        ->withWeight(120)
        ->withSettings(
            factory(ProductSettings::class)
                ->withExportAgeCheck(TriStateService::DISABLED)
                ->withExportLargeFormat(TriStateService::ENABLED)
        )
        ->store();
}

it('exposes inherited options', function () {
    setup();

    $order = factory(PdkOrder::class)
        ->withLines([factory(PdkOrderLine::class)->withProduct('product-1')])
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('postnl')
                ->withShipmentOptions(factory(ShipmentOptions::class)->withReturn(TriStateService::ENABLED))
        )
        ->make();

    $context = factory(OrderDataContext::class)
        ->with($order->getAttributes())
        ->make();

    expect($context->deliveryOptions->shipmentOptions->toArrayWithoutNull())
        ->toEqual([
            ShipmentOptions::INSURANCE         => TriStateService::INHERIT,
            ShipmentOptions::AGE_CHECK         => TriStateService::INHERIT,
            ShipmentOptions::HIDE_SENDER       => TriStateService::INHERIT,
            ShipmentOptions::LARGE_FORMAT      => TriStateService::INHERIT,
            ShipmentOptions::ONLY_RECIPIENT    => TriStateService::INHERIT,
            ShipmentOptions::DIRECT_RETURN     => TriStateService::ENABLED,
            ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::INHERIT,
            ShipmentOptions::SIGNATURE         => TriStateService::INHERIT,
            ShipmentOptions::TRACKED           => TriStateService::INHERIT,
            ShipmentOptions::RECEIPT_CODE      => TriStateService::INHERIT,
            ShipmentOptions::COLLECT           => TriStateService::INHERIT,
            ShipmentOptions::EXCLUDE_PARCEL_LOCKERS => TriStateService::INHERIT,
            ShipmentOptions::FRESH_FOOD        => TriStateService::INHERIT,
            ShipmentOptions::FROZEN            => TriStateService::INHERIT,
        ])
        ->and($context->inheritedDeliveryOptions->toArrayWithoutNull())
        ->toEqual([
            'postnl'          => [
                DeliveryOptions::LABEL_AMOUNT     => 1,
                DeliveryOptions::DELIVERY_TYPE    => DeliveryOptions::DEFAULT_DELIVERY_TYPE_NAME,
                DeliveryOptions::PACKAGE_TYPE     => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
                DeliveryOptions::SHIPMENT_OPTIONS => [
                    ShipmentOptions::INSURANCE         => TriStateService::DISABLED,
                    // Enabled by carrier, but disabled by product
                    ShipmentOptions::AGE_CHECK         => TriStateService::DISABLED,
                    ShipmentOptions::HIDE_SENDER       => TriStateService::DISABLED,
                    // Enabled by product
                    ShipmentOptions::LARGE_FORMAT      => TriStateService::ENABLED,
                    ShipmentOptions::ONLY_RECIPIENT    => TriStateService::DISABLED,
                    ShipmentOptions::DIRECT_RETURN     => TriStateService::DISABLED,
                    ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::DISABLED,
                    // Enabled by carrier
                    ShipmentOptions::SIGNATURE         => TriStateService::ENABLED,
                    ShipmentOptions::TRACKED           => TriStateService::DISABLED,
                    ShipmentOptions::RECEIPT_CODE      => TriStateService::DISABLED,
                    ShipmentOptions::COLLECT           => TriStateService::DISABLED,
                    ShipmentOptions::EXCLUDE_PARCEL_LOCKERS => TriStateService::DISABLED,
                    ShipmentOptions::FRESH_FOOD        => TriStateService::DISABLED,
                    ShipmentOptions::FROZEN            => TriStateService::DISABLED,
                ],
            ],
            'dhlforyou:12345' => [
                DeliveryOptions::LABEL_AMOUNT     => 1,
                DeliveryOptions::DELIVERY_TYPE    => DeliveryOptions::DEFAULT_DELIVERY_TYPE_NAME,
                DeliveryOptions::PACKAGE_TYPE     => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
                DeliveryOptions::SHIPMENT_OPTIONS => [
                    ShipmentOptions::INSURANCE         => TriStateService::DISABLED,
                    ShipmentOptions::AGE_CHECK         => TriStateService::DISABLED,
                    ShipmentOptions::HIDE_SENDER       => TriStateService::DISABLED,
                    // Enabled by product
                    ShipmentOptions::LARGE_FORMAT      => TriStateService::ENABLED,
                    // Enabled by carrier
                    ShipmentOptions::ONLY_RECIPIENT    => TriStateService::ENABLED,
                    ShipmentOptions::DIRECT_RETURN     => TriStateService::DISABLED,
                    ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::DISABLED,
                    ShipmentOptions::SIGNATURE         => TriStateService::DISABLED,
                    ShipmentOptions::TRACKED           => TriStateService::DISABLED,
                    ShipmentOptions::RECEIPT_CODE      => TriStateService::DISABLED,
                    ShipmentOptions::COLLECT           => TriStateService::DISABLED,
                    ShipmentOptions::EXCLUDE_PARCEL_LOCKERS => TriStateService::DISABLED,
                    ShipmentOptions::FRESH_FOOD        => TriStateService::DISABLED,
                    ShipmentOptions::FROZEN            => TriStateService::DISABLED,
                ],
            ],
        ]);
});

it('gets digital stamp ranges', function () {
    $context = factory(OrderDataContext::class)->make();

    expect($context->digitalStampRanges)
        ->toBeArray()
        ->and($context->digitalStampRanges)->not->toBeEmpty()
        ->and($context->digitalStampRanges)->each->toBeArray()
        ->and($context->digitalStampRanges)->each->toHaveKeys(['min', 'max', 'average']);
});

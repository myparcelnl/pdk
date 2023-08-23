<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('exposes inherited options', function () {
    factory(CarrierSettings::class, 'fake')
        ->withExportAgeCheck(true)
        ->withExportSignature(true)
        ->store();

    factory(PdkProduct::class)
        ->withExternalIdentifier('product-1')
        ->withSettings(
            factory(ProductSettings::class)
                ->withExportAgeCheck(TriStateService::DISABLED)
                ->withExportLargeFormat(TriStateService::ENABLED)
        )
        ->store();

    $order = factory(PdkOrder::class)
        ->withLines([factory(PdkOrderLine::class)->withProduct('product-1')])
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('fake')
                ->withShipmentOptions(factory(ShipmentOptions::class)->withOnlyRecipient(TriStateService::ENABLED))
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
            ShipmentOptions::ONLY_RECIPIENT    => TriStateService::ENABLED,
            ShipmentOptions::DIRECT_RETURN     => TriStateService::INHERIT,
            ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::INHERIT,
            ShipmentOptions::SIGNATURE         => TriStateService::INHERIT,
        ])
        ->and($context->inheritedDeliveryOptions->shipmentOptions->toArrayWithoutNull())
        ->toEqual([
            ShipmentOptions::LABEL_DESCRIPTION => '',
            ShipmentOptions::INSURANCE         => TriStateService::DISABLED,
            ShipmentOptions::AGE_CHECK         => TriStateService::DISABLED,
            ShipmentOptions::HIDE_SENDER       => TriStateService::DISABLED,
            ShipmentOptions::LARGE_FORMAT      => TriStateService::ENABLED,
            ShipmentOptions::ONLY_RECIPIENT    => TriStateService::DISABLED,
            ShipmentOptions::DIRECT_RETURN     => TriStateService::DISABLED,
            ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::DISABLED,
            ShipmentOptions::SIGNATURE         => TriStateService::ENABLED,
        ]);
});

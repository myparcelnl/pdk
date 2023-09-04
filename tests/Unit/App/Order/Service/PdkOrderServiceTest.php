<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Service;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

afterEach(function () {
    Pdk::get(PdkProductRepositoryInterface::class)
        ->reset();
});

it('calculates options', function (
    bool                           $carrierSetting,
    int                            $productSetting,
    int                            $shipmentOption,
    int                            $result,
    OrderOptionDefinitionInterface $definition
) {
    $fakeCarrier = factory(Carrier::class)
        ->withName('fake')
        ->withCapabilities(factory(CarrierCapabilities::class)->withAllOptions())
        ->make();

    factory(CarrierSettings::class, $fakeCarrier->externalIdentifier)
        ->with([$definition->getCarrierSettingsKey() => $carrierSetting])
        ->store();

    factory(PdkProductCollection::class)
        ->push(
            factory(PdkProduct::class)->withExternalIdentifier('PDK-A'),
            factory(PdkProduct::class)
                ->withExternalIdentifier('PDK-B')
                ->withSettings([$definition->getProductSettingsKey() => $productSetting])
        )
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($fakeCarrier)
                ->withShipmentOptions([$definition->getShipmentOptionsKey() => $shipmentOption])
        )
        ->withLines(
            factory(PdkOrderLineCollection::class)->push(
                factory(PdkOrderLine::class)->withProduct('PDK-A'),
                factory(PdkOrderLine::class)->withProduct('PDK-B')
            )
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    $finalValue = $newOrder->deliveryOptions->shipmentOptions->getAttribute($definition->getShipmentOptionsKey());

    expect($finalValue)->toBe($result);
})
    ->with('triState3BoolFirst')
    ->with('all shipment options');

<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Service\PdkOrderOptionsService;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('disables options that are not allowed in carrier', function (OrderOptionDefinitionInterface $definition) {
    $reset = mockPdkProperty('orderCalculators', [AllowedInCarrierCalculator::class]);

    $option = $definition->getCapabilitiesOptionsKey();

    $fakeCarrierFactory = factory(Carrier::class)
        ->withCapabilityShipmentOptions([$option]);

    factory(Shop::class)
        ->withCarriers(factory(CarrierCollection::class)->push($fakeCarrierFactory))
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($fakeCarrierFactory)
                ->withAllShipmentOptions()
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsService::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->shipmentOptions->toArray())->toHaveKeysAndValues([
        ShipmentOptions::AGE_CHECK         => TriStateService::DISABLED,
        ShipmentOptions::DIRECT_RETURN     => TriStateService::DISABLED,
        ShipmentOptions::HIDE_SENDER       => TriStateService::DISABLED,
        ShipmentOptions::LARGE_FORMAT      => TriStateService::DISABLED,
        ShipmentOptions::ONLY_RECIPIENT    => TriStateService::DISABLED,
        ShipmentOptions::PRIORITY_DELIVERY => TriStateService::DISABLED,
        ShipmentOptions::SAME_DAY_DELIVERY => TriStateService::DISABLED,
        ShipmentOptions::SIGNATURE         => TriStateService::DISABLED,
    ]);

    $reset();
})->with('all shipment options');

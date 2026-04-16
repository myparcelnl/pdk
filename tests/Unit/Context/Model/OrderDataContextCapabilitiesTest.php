<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('inherited delivery options enforce isRequired', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->withOptionRequired('requiresSignature')
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    factory(Settings::class)
        ->withCarrier('POSTNL', [CarrierSettings::EXPORT_SIGNATURE => 0])
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)->withCarrier('POSTNL')
        )
        ->make();

    $context = factory(OrderDataContext::class)
        ->with($order->getAttributes())
        ->make();

    $inherited = $context->inheritedDeliveryOptions->toArrayWithoutNull();

    expect($inherited['POSTNL']['shipmentOptions']['requiresSignature'])
        ->toBe(TriStateService::ENABLED);
});

it('inherited delivery options apply isSelectedByDefault when no explicit setting', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    // Store default carrier settings so EXPORT_SIGNATURE defaults to INHERIT (-1)
    factory(Settings::class)
        ->withCarrier('POSTNL')
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)->withCarrier('POSTNL')
        )
        ->make();

    $context = factory(OrderDataContext::class)
        ->with($order->getAttributes())
        ->make();

    $inherited = $context->inheritedDeliveryOptions->toArrayWithoutNull();

    expect($inherited['POSTNL']['shipmentOptions']['requiresSignature'])
        ->toBe(TriStateService::ENABLED);
});

it('inherited delivery options respect carrier setting over isSelectedByDefault', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    factory(Settings::class)
        ->withCarrier('POSTNL', [CarrierSettings::EXPORT_SIGNATURE => 0])
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)->withCarrier('POSTNL')
        )
        ->make();

    $context = factory(OrderDataContext::class)
        ->with($order->getAttributes())
        ->make();

    $inherited = $context->inheritedDeliveryOptions->toArrayWithoutNull();

    expect($inherited['POSTNL']['shipmentOptions']['requiresSignature'])
        ->toBe(TriStateService::DISABLED);
});

it('isRequired overrides even explicit carrier settings in inherited delivery options', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->withOptionRequired('requiresSignature')
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    factory(Settings::class)
        ->withCarrier('POSTNL', [CarrierSettings::EXPORT_SIGNATURE => 0])
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->withSignature(TriStateService::DISABLED)
                )
        )
        ->make();

    $context = factory(OrderDataContext::class)
        ->with($order->getAttributes())
        ->make();

    $inherited = $context->inheritedDeliveryOptions->toArrayWithoutNull();

    expect($inherited['POSTNL']['shipmentOptions']['requiresSignature'])
        ->toBe(TriStateService::ENABLED);
});

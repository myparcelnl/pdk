<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Service;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
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

uses()->group('options', 'tri-state');

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('forces ENABLED when isRequired is true even when all sources say DISABLED', function () {
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

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED);
});

it('does NOT force ENABLED when isRequired is false', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
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

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::DISABLED);
});

it('isSelectedByDefault applies when all sources are INHERIT', function () {
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
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->withSignature(TriStateService::INHERIT)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED);
});

it('carrier settings override isSelectedByDefault', function () {
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
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->withSignature(TriStateService::INHERIT)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::DISABLED);
});

it('shipment options override isSelectedByDefault', function () {
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
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->withSignature(TriStateService::DISABLED)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::DISABLED);
});

it('isSelectedByDefault applies in inherited delivery options flow', function () {
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
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->withSignature(TriStateService::INHERIT)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $flags    = PdkOrderOptionsServiceInterface::EXCLUDE_SHIPMENT_OPTIONS;
    $newOrder = $service->calculateShipmentOptions($order, $flags);

    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED);
});

it('isRequired resolves to ENABLED via capabilities default when carrier setting is INHERIT', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->withOptionRequired('requiresSignature')
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    // No explicit carrier settings stored — defaults to INHERIT (-1)
    factory(Settings::class)
        ->withCarrier('POSTNL')
        ->store();

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('POSTNL')
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)->withSignature(TriStateService::INHERIT)
                )
        )
        ->make();

    /** @var PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculateShipmentOptions($order);

    // CapabilitiesDefaultHelper provides ENABLED as fallback for isRequired,
    // post-resolution enforcement also forces ENABLED
    expect($newOrder->deliveryOptions->shipmentOptions->signature)->toBe(TriStateService::ENABLED);
});

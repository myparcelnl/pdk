<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Helper;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('options', 'tri-state');

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('returns ENABLED when isSelectedByDefault is true', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->withOptionSelectedByDefault('requiresSignature')
        ->store();

    // Clear the carrier repository cache so the updated carrier is re-fetched from the account.
    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier('POSTNL'))
        ->make();

    $helper = new CapabilitiesDefaultHelper($order);

    expect($helper->get(new SignatureDefinition()))->toEqual(TriStateService::ENABLED);
});

it('returns INHERIT when isSelectedByDefault is false', function () {
    factory(Carrier::class)
        ->withAllCapabilities()
        ->store();

    $storage = Pdk::get(StorageInterface::class);
    $storage->delete('carrier:POSTNL');
    $storage->delete('carrier:all');

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier('POSTNL'))
        ->make();

    $helper = new CapabilitiesDefaultHelper($order);

    expect($helper->get(new SignatureDefinition()))->toEqual(TriStateService::INHERIT);
});

it('returns INHERIT when definition has no capabilities key', function () {
    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(factory(DeliveryOptions::class)->withCarrier('POSTNL'))
        ->make();

    $helper = new CapabilitiesDefaultHelper($order);

    $definition = \Mockery::mock(OrderOptionDefinitionInterface::class);
    $definition->shouldReceive('getCapabilitiesOptionsKey')->andReturn(null);

    expect($helper->get($definition))->toEqual(TriStateService::INHERIT);
});

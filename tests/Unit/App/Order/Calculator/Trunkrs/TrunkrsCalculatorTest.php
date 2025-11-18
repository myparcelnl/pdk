<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Trunkrs;

use MyParcelNL\Pdk\App\Order\Calculator\General\CarrierSpecificCalculator;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('invokes trunkrs calculators and enforces age check rule', function () {
    $reset = mockPdkProperty('orderCalculators', [CarrierSpecificCalculator::class]);

    $order = factory(PdkOrder::class)
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('trunkrs')
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)
                        ->withAgeCheck(TriStateService::ENABLED)
                        ->withSignature(TriStateService::DISABLED)
                        ->withOnlyRecipient(TriStateService::DISABLED)
                )
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsServiceInterface::class);
    $newOrder = $service->calculate($order);

    $options = $newOrder->deliveryOptions->shipmentOptions;

    expect($options->signature)->toBe(TriStateService::ENABLED);
    expect($options->onlyRecipient)->toBe(TriStateService::ENABLED);

    $reset();
});



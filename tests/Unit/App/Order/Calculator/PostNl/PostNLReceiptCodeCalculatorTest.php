<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\PostNl;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('disables signature, only recipient, large format and return when receipt code is enabled', function () {
    $reset = mockPdkProperty('orderCalculators', [PostNLReceiptCodeCalculator::class]);

    $order = factory(PdkOrder::class)
        ->withShippingAddress(['cc' => 'NL'])
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withShipmentOptions(
                    factory(ShipmentOptions::class)
                        ->withSignature(TriStateService::ENABLED)
                        ->withOnlyRecipient(TriStateService::ENABLED)
                        ->withLargeFormat(TriStateService::ENABLED)
                        ->withReturn(TriStateService::ENABLED)
                        ->withReceiptCode(TriStateService::ENABLED)
                )
        )
        ->make();

    $calculator = new PostNLReceiptCodeCalculator($order);
    $calculator->calculate();

    $shipmentOptions = $order->deliveryOptions->shipmentOptions;

    expect($shipmentOptions->signature)->toBe(TriStateService::DISABLED)
        ->and($shipmentOptions->onlyRecipient)->toBe(TriStateService::DISABLED)
        ->and($shipmentOptions->largeFormat)->toBe(TriStateService::DISABLED)
        ->and($shipmentOptions->return)->toBe(TriStateService::DISABLED);

    $reset();
}); 
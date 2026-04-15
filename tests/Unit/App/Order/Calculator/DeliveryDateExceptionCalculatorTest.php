<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('nulls delivery date for Bpost', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::BPOST)->store();

    $order = new PdkOrder([
        'deliveryOptions' => [
            'carrier' => RefCapabilitiesSharedCarrierV2::BPOST,
            'date'    => '2026-04-20',
        ],
    ]);

    (new DeliveryDateExceptionCalculator($order))->calculate();

    expect($order->deliveryOptions->date)->toBeNull();
});

it('nulls delivery date for DPD', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::DPD)->store();

    $order = new PdkOrder([
        'deliveryOptions' => [
            'carrier' => RefCapabilitiesSharedCarrierV2::DPD,
            'date'    => '2026-04-20',
        ],
    ]);

    (new DeliveryDateExceptionCalculator($order))->calculate();

    expect($order->deliveryOptions->date)->toBeNull();
});

it('preserves delivery date for PostNL', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)->store();

    $order = new PdkOrder([
        'deliveryOptions' => [
            'carrier' => RefCapabilitiesSharedCarrierV2::POSTNL,
            'date'    => '2026-04-20',
        ],
    ]);

    (new DeliveryDateExceptionCalculator($order))->calculate();

    expect($order->deliveryOptions->date)->not->toBeNull();
});

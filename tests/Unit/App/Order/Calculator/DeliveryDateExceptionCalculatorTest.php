<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use DateTime;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

$futureDate = (new DateTime('+30 days'))->format('Y-m-d');

it('nulls delivery date for Bpost', function () use ($futureDate) {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::BPOST)->store();

    $order = new PdkOrder([
        'deliveryOptions' => [
            'carrier' => RefCapabilitiesSharedCarrierV2::BPOST,
            'date'    => $futureDate,
        ],
    ]);

    (new DeliveryDateExceptionCalculator($order))->calculate();

    expect($order->deliveryOptions->date)->toBeNull();
});

it('nulls delivery date for DPD', function () use ($futureDate) {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::DPD)->store();

    $order = new PdkOrder([
        'deliveryOptions' => [
            'carrier' => RefCapabilitiesSharedCarrierV2::DPD,
            'date'    => $futureDate,
        ],
    ]);

    (new DeliveryDateExceptionCalculator($order))->calculate();

    expect($order->deliveryOptions->date)->toBeNull();
});

it('preserves delivery date for PostNL', function () use ($futureDate) {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)->store();

    $order = new PdkOrder([
        'deliveryOptions' => [
            'carrier' => RefCapabilitiesSharedCarrierV2::POSTNL,
            'date'    => $futureDate,
        ],
    ]);

    (new DeliveryDateExceptionCalculator($order))->calculate();

    expect($order->deliveryOptions->date)->not->toBeNull();
});

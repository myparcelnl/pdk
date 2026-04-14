<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('propagates contractId from delivery options to created shipment', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)->store();

    $order = new PdkOrder([
        'externalIdentifier' => 'test-123',
        'deliveryOptions'    => [
            'carrier'    => RefCapabilitiesSharedCarrierV2::POSTNL,
            'contractId' => 42,
        ],
        'recipient' => [
            'cc' => 'NL', 'postalCode' => '2132WT', 'city' => 'Hoofddorp',
            'person' => 'Test', 'street' => 'Teststraat', 'number' => '1',
        ],
    ]);

    expect($order->createShipment()->contractId)->toBe('42');
});

it('leaves contractId null on shipment when delivery options has no contractId', function () {
    factory(Carrier::class)->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)->store();

    $order = new PdkOrder([
        'externalIdentifier' => 'test-456',
        'deliveryOptions'    => ['carrier' => RefCapabilitiesSharedCarrierV2::POSTNL],
        'recipient' => [
            'cc' => 'NL', 'postalCode' => '2132WT', 'city' => 'Hoofddorp',
            'person' => 'Test', 'street' => 'Teststraat', 'number' => '1',
        ],
    ]);

    expect($order->createShipment()->contractId)->toBeNull();
});

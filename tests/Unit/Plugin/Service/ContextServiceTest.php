<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Service\ContextService;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

it('gets context data', function (string $id, array $arguments) {
    /** @var \MyParcelNL\Pdk\Plugin\Service\ContextService $service */
    $service = Pdk::get(ContextService::class);

    $context = $service->createContexts([$id], $arguments);

    assertMatchesJsonSnapshot(json_encode($context->toArray()));
})->with([
    'global' => [
        'id'        => Context::ID_GLOBAL,
        'arguments' => [],
    ],

    'empty order data' => [
        'id'          => Context::ID_ORDER_DATA,
        'arguments'   => [],
        'expectation' => [
            'global'          => null,
            'orderData'       => [],
            'deliveryOptions' => null,
        ],
    ],

    'single order' => [
        'id'        => Context::ID_ORDER_DATA,
        'arguments' => [
            'order' => [
                'externalIdentifier' => '123',
            ],
        ],
    ],

    'multiple orders' => [
        'id'        => Context::ID_ORDER_DATA,
        'arguments' => [
            'order' => [
                [
                    'externalIdentifier' => '123',
                ],
                [
                    'externalIdentifier' => '124',
                ],
            ],
        ],
    ],

    'delivery options config' => [
        'id'        => Context::ID_DELIVERY_OPTIONS,
        'arguments' => [
            'order' => [
                'deliveryOptions' => [
                    'carrier'     => CarrierOptions::CARRIER_POSTNL_NAME,
                    'packageType' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                ],
            ],
        ],
    ],
]);

it('handles invalid context keys', function () {
    /** @var \MyParcelNL\Pdk\Plugin\Service\ContextService $service */
    $service    = Pdk::get(ContextService::class);
    $contextBag = $service->createContexts(['random_word']);

    expect($contextBag->toArray())->toEqual([
        'global'          => null,
        'orderData'       => null,
        'deliveryOptions' => null,
    ]);
});

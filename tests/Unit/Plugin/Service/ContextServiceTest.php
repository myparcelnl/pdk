<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Context\Service\ContextService;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

it('gets context data', function (string $id, array $arguments) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Context\Service\ContextService $service */
    $service = $pdk->get(ContextService::class);

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
        'id'        => Context::ID_CHECKOUT,
        'arguments' => [
            'order' => [
                'deliveryOptions' => [
                    'carrier'     => Carrier::CARRIER_POSTNL_NAME,
                    'packageType' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                ],
            ],
        ],
    ],
]);

it('handles invalid context keys', function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Context\Service\ContextService $service */
    $service    = $pdk->get(ContextService::class);
    $contextBag = $service->createContexts(['random_word']);

    expect($contextBag->toArray())->toEqual([
        'global'              => null,
        'orderData'           => null,
        'dynamic'             => null,
        'pluginSettingsView'  => null,
        'productSettingsView' => null,
        'checkout'            => null,
    ]);
});

<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Service;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

beforeEach(function () {
    factory(Settings::class)->store();
});

it('gets context data', function (string $id, array $arguments) {
    TestBootstrapper::hasAccount();

    /** @var \MyParcelNL\Pdk\Context\Service\ContextService $service */
    $service = Pdk::get(ContextService::class);

    $context = $service->createContexts([$id], $arguments);

    assertMatchesJsonSnapshot(json_encode($context->toArrayWithoutNull()));
})->with([
    'global' => [
        'id'        => Context::ID_GLOBAL,
        'arguments' => [],
    ],

    'empty order data' => [
        'id'        => Context::ID_ORDER_DATA,
        'arguments' => [],
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

    'empty product data' => [
        'id'        => Context::ID_PRODUCT_DATA,
        'arguments' => [],
    ],

    'single product' => [
        'id'        => Context::ID_PRODUCT_DATA,
        'arguments' => [
            'product' => [
                'externalIdentifier' => '123',
            ],
        ],
    ],

    'multiple products' => [
        'id'        => Context::ID_PRODUCT_DATA,
        'arguments' => [
            'product' => [
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
    /** @var \MyParcelNL\Pdk\Context\Service\ContextService $service */
    $service    = Pdk::get(ContextService::class);
    $contextBag = $service->createContexts(['random_word']);

    expect($contextBag->toArray())->toEqual([
        'checkout'            => null,
        'dynamic'             => null,
        'global'              => null,
        'orderData'           => null,
        'pluginSettingsView'  => null,
        'productData'         => null,
        'productSettingsView' => null,
    ]);
});

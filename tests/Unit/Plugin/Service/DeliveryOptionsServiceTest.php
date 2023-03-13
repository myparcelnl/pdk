<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Plugin\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('checkout');

usesShared(new UsesMockPdkInstance());

it('creates carrier settings', function (array $cart) {
    /** @var \MyParcelNL\Pdk\Plugin\Contract\DeliveryOptionsServiceInterface $service */
    $service = Pdk::get(DeliveryOptionsServiceInterface::class);

    $carrierSettings = $service->createAllCarrierSettings(new PdkCart($cart));

    assertMatchesJsonSnapshot(json_encode($carrierSettings));
})->with([
    'simple' => [
        'cart' => [
            'carrier' => ['name' => 'postnl'],
            'lines'   => [
                [
                    'quantity' => 1,
                    'product'  => [
                        'weight'        => 1,
                        'isDeliverable' => true,
                        'settings'      => [
                            'dropOffDelay' => 1,
                        ],
                    ],
                ],
                [
                    'quantity' => 1,
                    'product'  => [
                        'isDeliverable' => true,
                        'weight'        => 1,
                    ],
                ],
            ],
        ],
    ],

    'only virtual products' => [
        'cart' => [
            'carrier' => ['name' => 'postnl'],
            'lines'   => [
                [
                    'product' => [
                        'isDeliverable' => false,
                    ],
                ],
            ],
        ],
    ],
]);

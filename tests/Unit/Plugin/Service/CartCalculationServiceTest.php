<?php
/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Plugin\Service;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

const LINES_FITS_IN_MAILBOX = [
    [
        'quantity' => 1,
        'product'  => [
            'weight'        => 1,
            'isDeliverable' => true,
            'settings'      => [
                'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                'fitInMailbox' => 5,
            ],
        ],
    ],
    [
        'quantity' => 1,
        'product'  => [
            'isDeliverable' => true,
            'weight'        => 1,
            'settings'      => [
                'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                'fitInMailbox' => 5,
            ],
        ],
    ],
];

const LINES_DONT_FIT_MAILBOX = [
    [
        'quantity' => 1,
        'product'  => [
            'weight'        => 1,
            'isDeliverable' => true,
        ],
    ],
    [
        'quantity' => 1,
        'product'  => [
            'isDeliverable' => true,
            'weight'        => 1,
            'settings'      => [
                'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                'fitInMailbox' => 10,
            ],
        ],
    ],
];

const LINES_EXCEEDING_MAILBOX_SIZE = [
    [
        'quantity' => 5,
        'product'  => [
            'isDeliverable' => true,
            'weight'        => 1,
            'settings'      => [
                'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                'fitInMailbox' => 4,
            ],
        ],
    ],
];

uses()->group('checkout');

usesShared(new UsesMockPdkInstance());

it('calculates mailbox percentage', function (array $lines, float $expected) {
    /** @var \MyParcelNL\Pdk\Plugin\Contract\CartCalculationServiceInterface $service */
    $service = Pdk::get(CartCalculationServiceInterface::class);

    $percentage = $service->calculateMailboxPercentage(new PdkCart(['lines' => $lines]));

    expect($percentage)->toBe($expected);
})->with([
    'fits in mailbox'                  => [
        'lines'      => LINES_FITS_IN_MAILBOX,
        'percentage' => 40.0,
    ],
    'one item does not fit in mailbox' => [
        'lines'      => LINES_DONT_FIT_MAILBOX,
        'percentage' => INF,
    ],
    'items exceeding mailbox size'     => [
        'lines'      => LINES_EXCEEDING_MAILBOX_SIZE,
        'percentage' => 125.0,
    ],
]);

it('calculates allowed package types', function (array $lines, array $result) {
    /** @var \MyParcelNL\Pdk\Plugin\Contract\CartCalculationServiceInterface $service */
    $service = Pdk::get(CartCalculationServiceInterface::class);

    $allowedPackageTypes = $service->calculateAllowedPackageTypes(new PdkCart(['lines' => $lines]));

    expect($allowedPackageTypes)->toEqual($result);
})->with([
    'fits in mailbox'                  => [
        'lines'  => LINES_FITS_IN_MAILBOX,
        'result' => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME, DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME],
    ],
    'one item does not fit in mailbox' => [
        'lines'  => LINES_DONT_FIT_MAILBOX,
        'result' => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
    'items exceeding mailbox size'     => [
        'lines'  => LINES_EXCEEDING_MAILBOX_SIZE,
        'result' => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
]);

it('calculates shipping method in cart', function (array $lines, array $result) {
    $cart = new PdkCart(['lines' => $lines]);

    expect(Arr::except($cart->shippingMethod->toArray(), 'shippingAddress'))->toEqual($result);
})->with([
    'no product settings' => [
        'cart'   => [
            [
                'quantity' => 2,
                'product'  => [
                    'weight'        => 1000,
                    'isDeliverable' => true,
                ],
            ],
            [
                'quantity' => 3,
                'product'  => [
                    'isDeliverable' => true,
                    'weight'        => 1000,
                ],
            ],
        ],
        'result' => [
            'hasDeliveryOptions'  => true,
            'minimumDropOffDelay' => 0,
            'allowPackageTypes'   => [DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME],
        ],
    ],

    'product has minimum dropoff delay' => [
        'lines'  => [
            [
                'quantity' => 1,
                'product'  => [
                    'isDeliverable' => true,
                    'weight'        => 1000,
                ],
            ],
            [
                'quantity' => 1,
                'product'  => [
                    'isDeliverable' => true,
                    'settings'      => [
                        'dropOffDelay' => 2,
                    ],
                    'weight'        => 1000,
                ],
            ],
        ],
        'result' => [
            'hasDeliveryOptions'  => true,
            'minimumDropOffDelay' => 2,
            'allowPackageTypes'   => [DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME],
        ],
    ],

    'no deliverable products' => [
        'cart'   => [
            'carrier' => ['name' => 'postnl'],
            'lines'   => [
                [
                    'quantity' => 1,
                    'product'  => [
                        'isDeliverable' => false,
                    ],
                ],
            ],
        ],
        'result' => [
            'hasDeliveryOptions'  => false,
            'minimumDropOffDelay' => 0,
            'allowPackageTypes'   => [],
        ],
    ],
]);

<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Service;

use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

const LINES_FITS_IN_MAILBOX = [
    [
        'quantity' => 1,
        'product'  => [
            'weight'        => 1,
            'isDeliverable' => true,
            'settings'      => [
                'packageType'  => -1,
                'fitInMailbox' => -1,
            ],
            'parent' => [
                'weight'        => 1,
                'isDeliverable' => true,
                'settings' => [
                    'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                    'fitInMailbox' => 5,
                ],
            ]
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

const LINES_EXCEEDING_MAILBOX_MAXIMUM_WEIGHT = [
    [
        'quantity' => 5,
        'product'  => [
            'isDeliverable' => true,
            'weight'        => 500,
            'settings'      => [
                'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                'fitInMailbox' => 10,
            ],
        ],
    ],
];

const TOTAL_EXCEEDING_MAILBOX_MAXIMUM_WEIGHT = [
    [
        'quantity' => 4,
        'product'  => [
            'isDeliverable' => true,
            'weight'        => 500,
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
    /** @var \MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface $service */
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
    factory(OrderSettings::class)
        ->withEmptyMailboxWeight(200)
        ->store();

    /** @var \MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface $service */
    $service = Pdk::get(CartCalculationServiceInterface::class);

    $allowedPackageTypes = $service->calculateAllowedPackageTypes(new PdkCart(['lines' => $lines]));

    expect(Arr::pluck($allowedPackageTypes->toArray(), 'name'))->toEqual($result);
})->with([
    'fits in mailbox'                  => [
        'lines'  => LINES_FITS_IN_MAILBOX,
        'result' => [DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME, DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
    'one item does not fit in mailbox' => [
        'lines'  => LINES_DONT_FIT_MAILBOX,
        'result' => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
    'items exceeding mailbox weight'   => [
        'lines'  => LINES_EXCEEDING_MAILBOX_MAXIMUM_WEIGHT,
        'result' => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
    'total exceeding mailbox weight'   => [
        'lines'  => TOTAL_EXCEEDING_MAILBOX_MAXIMUM_WEIGHT,
        'result' => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
    'items exceeding mailbox size'     => [
        'lines'  => LINES_EXCEEDING_MAILBOX_SIZE,
        'result' => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
]);

it('calculates shipping method in cart', function (array $lines, array $result) {
    $cart = new PdkCart(['lines' => $lines]);

    expect($cart->shippingMethod->except('shippingAddress'))->toEqual($result);
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
            'id'                  => null,
            'name'                => null,
            'isEnabled'           => true,
            'allowedPackageTypes' => [
                [
                    'name' => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
                    'id'   => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
                ],
            ],
            'hasDeliveryOptions'  => true,
            'minimumDropOffDelay' => TriStateService::INHERIT,
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
            'id'                  => null,
            'name'                => null,
            'isEnabled'           => true,
            'hasDeliveryOptions'  => true,
            'minimumDropOffDelay' => 2,
            'allowedPackageTypes' => [
                [
                    'name' => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
                    'id'   => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
                ],
            ],
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
            'id'                  => null,
            'name'                => null,
            'isEnabled'           => true,
            'hasDeliveryOptions'  => false,
            'minimumDropOffDelay' => 0,
            'allowedPackageTypes' => [],
        ],
    ],
]);

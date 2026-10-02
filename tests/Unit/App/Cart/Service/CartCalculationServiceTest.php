<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Service;

use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
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
            'parent'        => [
                'weight'        => 1,
                'isDeliverable' => true,
                'settings'      => [
                    'packageType'  => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                    'fitInMailbox' => 5,
                ],
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
            'settings'      => [
                'fitInMailbox' => 0,
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

const SHIPPING_ADDRESS_NL = [
    'street'     => 'Straatnaam 2',
    'number'     => 'Appartement B',
    'cc'         => CountryCodes::CC_NL,
    'city'       => 'Stad',
    'postalCode' => '1000 BB',
    'region'     => 'Drenthe',
    'state'      => 'DT',
    'isBusiness' => false,
];

const SHIPPING_ADDRESS_EU = [
    'street'     => 'Straatnaam 2',
    'cc'         => CountryCodes::CC_FR,
    'city'       => 'Paris',
    'postalCode' => '1000 BB',
    'region'     => 'Paris',
    'state'      => 'CP',
];
const SHIPPING_ADDRESS_BE = [
    'street'       => '16',
    'number'       => 'Appartement B',
    'numberSuffix' => 'Appartement B',
    'cc'           => CountryCodes::CC_BE,
    'city'         => 'Antwerpen',
    'postalCode'   => '1000',
    'region'       => 'Antwerpen',
    'state'        => 'Current',
];

uses()->group('checkout');
usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

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

it('calculates shipping method in cart', function (array $lines, array $result) {
    $cart = new PdkCart(['lines' => $lines, 'shippingMethod' => ['shippingAddress' => SHIPPING_ADDRESS_NL]]);

    expect($cart->shippingMethod->toArray(Arrayable::SKIP_NULL))->toMatchArray($result);
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
            'isEnabled'            => true,

            'hasDeliveryOptions'   => true,
            'minimumDropOffDelay'  => TriStateService::INHERIT,
            'shippingAddress'      => SHIPPING_ADDRESS_NL,
            'excludeParcelLockers' => false,
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
            'isEnabled'            => true,
            'hasDeliveryOptions'   => true,
            'minimumDropOffDelay'  => 2,

            'shippingAddress'      => SHIPPING_ADDRESS_NL,
            'excludeParcelLockers' => false,
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
            'isEnabled'            => true,
            'hasDeliveryOptions'   => false,

            'shippingAddress'      => SHIPPING_ADDRESS_NL,
            'excludeParcelLockers' => false,
        ],
    ],

    'product with 18+ excludes parcel lockers' => [
        'lines'  => [
            [
                'quantity' => 1,
                'product'  => [
                    'isDeliverable' => true,
                    'weight'        => 1000,
                    'settings'      => [
                        'exportAgeCheck' => TriStateService::ENABLED,
                    ],
                ],
            ],
        ],
        'result' => [
            'isEnabled'            => true,
            'hasDeliveryOptions'   => true,

            'minimumDropOffDelay'  => TriStateService::INHERIT,
            'shippingAddress'      => SHIPPING_ADDRESS_NL,
            'excludeParcelLockers' => true,
        ],
    ],

    'product with explicit excludeParcelLockers setting' => [
        'lines'  => [
            [
                'quantity' => 1,
                'product'  => [
                    'isDeliverable' => true,
                    'weight'        => 1000,
                    'settings'      => [
                        'excludeParcelLockers' => TriStateService::ENABLED,
                    ],
                ],
            ],
        ],
        'result' => [
            'isEnabled'            => true,
            'hasDeliveryOptions'   => true,

            'minimumDropOffDelay'  => TriStateService::INHERIT,
            'shippingAddress'      => SHIPPING_ADDRESS_NL,
            'excludeParcelLockers' => true,
        ],
    ],
]);

it('resolves cart package types from merged settings so child products inherit parent package type', function () {
    /** @var \MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface $service */
    $service = Pdk::get(CartCalculationServiceInterface::class);

    $cart = new PdkCart([
        'lines' => [
            [
                'quantity' => 1,
                'product'  => [
                    'isDeliverable' => true,
                    'weight'        => 1,
                    'settings'      => [
                        'packageType' => TriStateService::INHERIT,
                    ],
                    'parent'        => [
                        'isDeliverable' => true,
                        'weight'        => 1,
                        'settings'      => [
                            'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    expect($service->getCartPackageTypes($cart))->toBe([DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME]);
});


it('only exposes a known shipping weight without changing the original cart', function (array $lines, int $packaging, ?int $expected) {
    factory(OrderSettings::class)->withEmptyParcelWeight($packaging)->store();

    $cart = new PdkCart(['lines' => $lines]);
    $originalLines = $cart->lines;
    $originalData = $cart->toArray();
    $originalWeight = $originalLines->getTotalWeight();

    $service = Pdk::get(CartCalculationServiceInterface::class);

    expect($service->getKnownCartWeightForPackageType($cart, DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME))
        ->toBe($expected)
        ->and($cart->lines)->toBe($originalLines)
        ->and($cart->toArray())->toBe($originalData)
        ->and($cart->lines->getTotalWeight())->toBe($originalWeight);
})->with(function () {
    $line = static function (?int $weight, int $quantity = 1, bool $deliverable = true): array {
        return ['quantity' => $quantity, 'product' => ['weight' => $weight, 'isDeliverable' => $deliverable]];
    };

    return [
        'quantities and packaging once' => [[$line(10000, 3)], 250, 30250],
        'missing weight'                => [[$line(null)], 250, null],
        'packaging alone is unknown'    => [[$line(0)], 250, null],
        'negative weight'               => [[$line(-1)], 250, null],
        'partially known cart'          => [[$line(10000), $line(null)], 250, null],
        'zero in partially known cart'  => [[$line(10000), $line(0)], 250, null],
        'virtual unknown item'          => [[$line(1000), $line(null, 1, false)], 250, 1250],
        'virtual known item'            => [[$line(1000), $line(5000, 2, false)], 250, 1250],
        'zero quantity unknown item'    => [[$line(1000), $line(null, 0)], 250, 1250],
        'negative quantity item'        => [[$line(1000), $line(500, -1)], 250, 1250],
        'only virtual items'            => [[$line(1000, 1, false)], 250, null],
        'no positive quantities'        => [[$line(1000, 0)], 250, null],
        'empty cart'                    => [[], 250, null],
        'genuine one gram'              => [[$line(1)], 0, 1],
    ];
});

it('uses the selected package type when calculating known weight', function () {
    factory(OrderSettings::class)->withEmptyParcelWeight(250)->withEmptyMailboxWeight(50)->store();
    $cart = new PdkCart(['lines' => [['quantity' => 2, 'product' => ['weight' => 500, 'isDeliverable' => true]]]]);
    $service = Pdk::get(CartCalculationServiceInterface::class);

    expect($service->getKnownCartWeightForPackageType($cart, DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME))->toBe(1250)
        ->and($service->getKnownCartWeightForPackageType($cart, DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME))->toBe(1050);
});

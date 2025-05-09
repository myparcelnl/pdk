<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Cart\Service;

use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
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

const SHIPPING_ADDRESS_NL = [
    'street'   => 'Straatnaam 2',
    'number'   => 'Appartement B',
    'cc'         => CountryCodes::CC_NL,
    'city'       => 'Stad',
    'postalCode' => '1000 BB',
    'region'     => 'Drenthe',
    'state'      => 'DT',
];

const SHIPPING_ADDRESS_EU = [
    'street'   => 'Straatnaam 2',
    'cc'         => CountryCodes::CC_FR,
    'city'       => 'Paris',
    'postalCode' => '1000 BB',
    'region'     => 'Paris',
    'state'      => 'CP',
];
const SHIPPING_ADDRESS_BE = [
    'street'   => '16',
    'number'   => 'Appartement B',
    'numberSuffix' => 'Appartement B',
    'cc'         => CountryCodes::CC_BE,
    'city'       => 'Antwerpen',
    'postalCode' => '1000',
    'region'     => 'Antwerpen',
    'state'      => 'Current',
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

it(
    'calculates allowed package types',
    function (array $lines, array $shippingAddress, array $result, bool $allowInternationalMailbox = false) {
        factory(Settings::class)
            ->withCarrierPostNl(
                factory(CarrierSettings::class, Carrier::CARRIER_POSTNL_NAME)
                    ->withAllowInternationalMailbox($allowInternationalMailbox)
                    ->withDeliveryOptionsEnabled(true)
            )
            ->withOrder(
                factory(OrderSettings::class)
                    ->withEmptyMailboxWeight(200)
            )
            ->store();

        /** @var \MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface $service */
        $service = Pdk::get(CartCalculationServiceInterface::class);

        $allowedPackageTypes = $service->calculateAllowedPackageTypes(
            new PdkCart(['lines' => $lines, 'shippingMethod' => ['shippingAddress' => $shippingAddress]])
        );

        expect(Arr::pluck($allowedPackageTypes->toArray(), 'name'))->toEqual($result);
    }
)->with([
    'fits in mailbox'                  => [
        'lines'   => LINES_FITS_IN_MAILBOX,
        'address' => SHIPPING_ADDRESS_NL,
        'result'  => [DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME, DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
    'fits in mailbox BE, allowed'      => [
        'lines'                     => LINES_FITS_IN_MAILBOX,
        'address'                   => SHIPPING_ADDRESS_BE,
        'result'                    => [
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ],
        'allowInternationalMailbox' => true,
    ],
    'fits in mailbox BE, not allowed'  => [
        'lines'   => LINES_FITS_IN_MAILBOX,
        'address' => SHIPPING_ADDRESS_BE,
        'result'  => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
    'fits in mailbox EU, allowed'      => [
        'lines'                     => LINES_FITS_IN_MAILBOX,
        'address'                   => SHIPPING_ADDRESS_EU,
        'result'                    => [
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ],
        'allowInternationalMailbox' => true,
    ],
    'fits in mailbox EU, not allowed'  => [
        'lines'   => LINES_FITS_IN_MAILBOX,
        'address' => SHIPPING_ADDRESS_EU,
        'result'  => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
    'one item does not fit in mailbox' => [
        'lines'   => LINES_DONT_FIT_MAILBOX,
        'address' => SHIPPING_ADDRESS_NL,
        'result'  => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
    'items exceeding mailbox weight'   => [
        'lines'   => LINES_EXCEEDING_MAILBOX_MAXIMUM_WEIGHT,
        'address' => SHIPPING_ADDRESS_NL,
        'result'  => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
    'total exceeding mailbox weight'   => [
        'lines'   => TOTAL_EXCEEDING_MAILBOX_MAXIMUM_WEIGHT,
        'address' => SHIPPING_ADDRESS_NL,
        'result'  => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
    'items exceeding mailbox size'     => [
        'lines'   => LINES_EXCEEDING_MAILBOX_SIZE,
        'address' => SHIPPING_ADDRESS_NL,
        'result'  => [DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
    ],
]);

it('calculates shipping method in cart', function (array $lines, array $result) {
    $cart = new PdkCart(['lines' => $lines, 'shippingMethod' => ['shippingAddress' => SHIPPING_ADDRESS_NL]]);

    expect($cart->shippingMethod->toArray(Arrayable::SKIP_NULL))->toEqual($result);
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
            'isEnabled'           => true,
            'allowedPackageTypes' => [
                [
                    'name' => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
                    'id'   => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
                ],
            ],
            'hasDeliveryOptions'  => true,
            'minimumDropOffDelay' => TriStateService::INHERIT,
            'shippingAddress'     => SHIPPING_ADDRESS_NL,
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
            'isEnabled'           => true,
            'hasDeliveryOptions'  => true,
            'minimumDropOffDelay' => 2,
            'allowedPackageTypes' => [
                [
                    'name' => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
                    'id'   => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
                ],
            ],
            'shippingAddress'     => SHIPPING_ADDRESS_NL,
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
            'isEnabled'           => true,
            'hasDeliveryOptions'  => false,
            'allowedPackageTypes' => [],
            'shippingAddress'     => SHIPPING_ADDRESS_NL,
        ],
    ],
]);

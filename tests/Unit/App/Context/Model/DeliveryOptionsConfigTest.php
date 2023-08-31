<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkProductRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        PdkProductRepositoryInterface::class => autowire(MockPdkProductRepository::class)->constructor([
            ['externalIdentifier' => 'PDK-1', 'isDeliverable' => true],
            ['externalIdentifier' => 'PDK-2', 'isDeliverable' => true, 'exportSignature' => true],
        ]),
    ])
);

it('can be instantiated', function () {
    $config = new DeliveryOptionsConfig();

    $pickupLocationsDefaultView = Settings::get(
        CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW,
        CheckoutSettings::ID
    );

    expect($config)
        ->toBeInstanceOf(DeliveryOptionsConfig::class)
        ->and($config->toArray())
        ->toEqual([
            'allowRetry'                 => false,
            'apiBaseUrl'                 => 'https://api.myparcel.nl',
            'basePrice'                  => 0,
            'carrierSettings'            => [],
            'currency'                   => 'EUR',
            'locale'                     => 'nl-NL',
            'packageType'                => 'package',
            'pickupLocationsDefaultView' => $pickupLocationsDefaultView,
            'platform'                   => 'myparcel',
            'showPriceSurcharge'         => false,
            'priceStandardDelivery'      => 0,
        ]);
});

it('can be instantiated from a cart', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkProductRepository $productRepository */
    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);

    $cart = new PdkCart([
        'shipmentPrice' => 695,
        'lines'         => [
            [
                'quantity' => 1,
                'product'  => $productRepository->getProduct('PDK-1'),
            ],
            [
                'quantity' => 1,
                'product'  => $productRepository->getProduct('PDK-2'),
            ],
        ],
    ]);

    $config = DeliveryOptionsConfig::fromCart($cart);

    expect($config)
        ->toBeInstanceOf(DeliveryOptionsConfig::class)
        ->and($config->toArray())
        ->toEqual([
            'allowRetry'                 => false,
            'basePrice'                  => 695,
            'carrierSettings'            => [
                'postnl'         => [
                    'allowDeliveryOptions'         => false,
                    'allowEveningDelivery'         => false,
                    'allowMondayDelivery'          => false,
                    'allowMorningDelivery'         => false,
                    'allowOnlyRecipient'           => false,
                    'allowPickupLocations'         => false,
                    'allowSameDayDelivery'         => false,
                    'allowSaturdayDelivery'        => false,
                    'allowSignature'               => false,
                    'featureShowDeliveryDate'      => true,
                    'priceEveningDelivery'         => 0.0,
                    'priceMorningDelivery'         => 0.0,
                    'priceOnlyRecipient'           => 0.0,
                    'pricePackageTypeDigitalStamp' => 0.0,
                    'pricePackageTypeMailbox'      => 0.0,
                    'pricePickup'                  => 0.0,
                    'priceSameDayDelivery'         => 0.0,
                    'priceSignature'               => 0.0,
                    'priceStandardDelivery'        => 0.0,
                    'deliveryDaysWindow'           => 7,
                    'dropOffDelay'                 => 0,
                    'cutoffTime'                   => null,
                    'cutoffTimeSameDay'            => null,
                ],
                'dhlforyou:8277' => [
                    'allowDeliveryOptions'         => false,
                    'allowEveningDelivery'         => false,
                    'allowMondayDelivery'          => false,
                    'allowMorningDelivery'         => false,
                    'allowOnlyRecipient'           => false,
                    'allowPickupLocations'         => false,
                    'allowSameDayDelivery'         => false,
                    'allowSaturdayDelivery'        => false,
                    'allowSignature'               => false,
                    'featureShowDeliveryDate'      => true,
                    'priceEveningDelivery'         => 0.0,
                    'priceMorningDelivery'         => 0.0,
                    'priceOnlyRecipient'           => 0.0,
                    'pricePackageTypeDigitalStamp' => 0.0,
                    'pricePackageTypeMailbox'      => 0.0,
                    'pricePickup'                  => 0.0,
                    'priceSameDayDelivery'         => 0.0,
                    'priceSignature'               => 0.0,
                    'priceStandardDelivery'        => 0.0,
                    'deliveryDaysWindow'           => 7,
                    'dropOffDelay'                 => 0,
                    'cutoffTime'                   => null,
                    'cutoffTimeSameDay'            => null,
                ],
            ],
            'currency'                   => 'EUR',
            'locale'                     => 'nl-NL',
            'packageType'                => 'package',
            'pickupLocationsDefaultView' => null,
            'platform'                   => 'myparcel',
            'showPriceSurcharge'         => false,
            'apiBaseUrl'                 => 'https://api.myparcel.nl',
            'priceStandardDelivery'      => 0,
        ]);
});

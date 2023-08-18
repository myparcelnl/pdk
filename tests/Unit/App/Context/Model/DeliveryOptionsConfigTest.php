<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use function MyParcelNL\Pdk\Tests\factory;

beforeEach(function () {
    factory(PdkProduct::class)
        ->withExternalIdentifier('PDK-1')
        ->withIsDeliverable(true)
        ->store();

    factory(PdkProduct::class)
        ->withExternalIdentifier('PDK-2')
        ->withIsDeliverable(true)
        ->withSettings(['exportSignature' => true])
        ->store();
});

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
        ]);
});

it('can be instantiated from a cart', function () {
    /** @var \MyParcelNL\Pdk\App\Order\Repository\MockPdkProductRepository $productRepository */
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
        ]);
});

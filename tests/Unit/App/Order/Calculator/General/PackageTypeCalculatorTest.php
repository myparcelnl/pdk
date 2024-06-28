<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Service\PdkOrderOptionsService;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use function MyParcelNL\Pdk\Tests\mockPlatform;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesEachMockPdkInstance());

it('calculates package type', function (
    string $platform,
    array  $options,
    string $result
) {
    mockPlatform($platform);
    mockPdkProperties([
        'orderCalculators' => [PackageTypeCalculator::class],
    ]);

    $fakeCarrier = factory(Carrier::class)
        ->withCapabilities(factory(CarrierCapabilities::class)->withAllOptions());

    $order = factory(PdkOrder::class)
        ->withShippingAddress(
            factory(ShippingAddress::class)->withCc($options['cc'] ?? Platform::get('localCountry'))
        )
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($fakeCarrier)
                ->withPackageType($options['packageType'])
                ->withAllShipmentOptions()
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsService::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->packageType)->toBe($result);
}
)
    ->with('platforms')
    ->with([
        'local country, package type package' => [
            'options' => ['packageType' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME],
            'result'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ],

        'local country, package type letter' => [
            'options' => ['packageType' => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME],
            'result'  => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
        ],

        'non-local country, package type package' => [
            'options' => [
                'cc'          => CountryCodes::CC_GB,
                'packageType' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            ],
            'result'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ],

        'non-local country, package type letter' => [
            'options' => [
                'cc'          => CountryCodes::CC_GB,
                'packageType' => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
            ],
            'result'  => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
        ],

        'non-local country, package type mailbox' => [
            'options' => [
                'cc'          => CountryCodes::CC_GB,
                'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ],
            'result'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ],

        'non-local country, package type package small' => [
            'options' => [
                'cc'          => CountryCodes::CC_GB,
                'packageType' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
            ],
            'result'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
        ],
    ]);

// - contract setting aan
// - contract setting uit
// - carrier setting aan
// - carrier setting uit

it('calculates international mailbox', function (
    string $platform,
    string $country,
    string $carrierName,
    bool   $carrierSetting,
    bool   $accountFlag
) {
    mockPlatform($platform);
    mockPdkProperties([
        'orderCalculators' => [PackageTypeCalculator::class],
    ]);

    factory(CarrierSettings::class, $carrierName)
        ->withAllowInternationalMailbox($carrierSetting)
        ->store();

    factory(AccountSettings::class)
        ->withGeneralSettings([
            'hasCarrierSmallPackageContract' => $accountFlag,
        ])
        ->store();

    $fakeCarrier = factory(Carrier::class)
        ->withCapabilities(factory(CarrierCapabilities::class)->withAllOptions());

    $order = factory(PdkOrder::class)
        ->withShippingAddress(
            factory(ShippingAddress::class)->withCc($options['cc'] ?? Platform::get('localCountry'))
        )
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($fakeCarrier)
                ->withPackageType($options['packageType'])
                ->withAllShipmentOptions()
        )
        ->make();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsService::class);
    $newOrder = $service->calculate($order);

    expect($newOrder->deliveryOptions->packageType)->toBe($result);
})
    ->with('platforms')
    ->with([
        'gb, account flag on, carrier setting on'   => [
            CountryCodes::CC_GB,
            true,
            true,
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        ],
        'gb, account flag on, carrier setting off'  => [
            CountryCodes::CC_GB,
            true,
            false,
            DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ],
        'gb, account flag off, carrier setting on'  => [
            CountryCodes::CC_GB,
            false,
            true,
            DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
        ],
        'gb, account flag off, carrier setting off' => [
            // todo: maak het zoiets:
            'country'        => CountryCodes::CC_GB,
            'carrier'        => '...',
            'carrierSetting' => false,
            'accountFlag'    => false,
            'packageType'    => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
        ],
    ])
    ->with([
        'non-local country, package type mailbox, postnl' => [
            'options'                => [
                'cc'          => CountryCodes::CC_GB,
                'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ],
            'result'                 => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'accountFactory'         => function () {
                return factory(Account::class)->withGeneralSettings([
                    'hasCarrierSmallPackageContract' => true,
                ]);
            },
            'carrierSettingsFactory' => function () {
                return factory(CarrierSettings::class, Carrier::CARRIER_POSTNL_NAME)
                    ->withAllowInternationalMailbox(true);
            },
        ],

        'non-local country, package type mailbox, dhl' => [
            'options'                => [
                'cc'          => CountryCodes::CC_GB,
                'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ],
            'result'                 => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            'accountFactory'         => function () {
                return factory(Account::class)->withGeneralSettings([
                    'hasCarrierSmallPackageContract' => true,
                ]);
            },
            'carrierSettingsFactory' => function () {
                return factory(CarrierSettings::class, Carrier::CARRIER_DHL_FOR_YOU_NAME)
                    ->withAllowInternationalMailbox(true);
            },
        ],
    ]);

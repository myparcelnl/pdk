<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\Account\Model\AccountGeneralSettings;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Service\PdkOrderOptionsService;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use function MyParcelNL\Pdk\Tests\mockPlatform;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesSnapshot;

const CARRIER_POSTNL      = [
    'carrierExternalIdentifier' => 'postnl:123',
    'carrierName'               => 'postnl',
];
const CARRIER_DHL_FOR_YOU = [
    'carrierExternalIdentifier' => 'dhlforyou:123',
    'carrierName'               => 'dhlforyou',
];
const CARRIER_DPD         = [
    'carrierExternalIdentifier' => 'dpd',
    'carrierName'               => 'dpd',
];
const CARRIERS            = [
    CARRIER_POSTNL,
    CARRIER_DHL_FOR_YOU,
    CARRIER_DPD,
];

const ACCOUNT_FLAG_ON_CARRIER_SETTING_ON = [
    'accountHasCarrierSmallPackageContract' => true,
    'carrierHasInternationalMailboxAllowed' => true,
];

const ACCOUNT_FLAG_ON_CARRIER_SETTING_OFF = [
    'accountHasCarrierSmallPackageContract' => true,
    'carrierHasInternationalMailboxAllowed' => false,
];

const ACCOUNT_FLAG_OFF_CARRIER_SETTING_ON = [
    'accountHasCarrierSmallPackageContract' => false,
    'carrierHasInternationalMailboxAllowed' => true,
];

const ACCOUNT_FLAG_OFF_CARRIER_SETTING_OFF = [
    'accountHasCarrierSmallPackageContract' => false,
    'carrierHasInternationalMailboxAllowed' => false,
];

const CONFIG = [
    ACCOUNT_FLAG_ON_CARRIER_SETTING_ON,
    ACCOUNT_FLAG_ON_CARRIER_SETTING_OFF,
    ACCOUNT_FLAG_OFF_CARRIER_SETTING_ON,
    ACCOUNT_FLAG_OFF_CARRIER_SETTING_OFF,
];

const DESTINATION_INTERNATIONAL_COUNTRIES = [
    CountryCodes::CC_FR,
    CountryCodes::CC_US,
    CountryCodes::CC_BE,
];

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

it('calculates international mailbox', function (
    $platform,
    $country,
    $carrierExternalIdentifier,
    $carrierName,
    $accountHasCarrierSmallPackageContract,
    $carrierHasInternationalMailboxAllowed
) {
    mockPlatform($platform);
    mockPdkProperties([
        'orderCalculators' => [PackageTypeCalculator::class],
    ]);

    $fakeCarrier = factory(Carrier::class)
        ->withExternalIdentifier($carrierExternalIdentifier)
        ->withCapabilities(
            factory(CarrierCapabilities::class)->fromCarrier($carrierName)
        )
        ->make();

    factory(CarrierSettings::class, $fakeCarrier->externalIdentifier)
        ->withAllowInternationalMailbox($carrierHasInternationalMailboxAllowed)
        ->store();

    $order = factory(PdkOrder::class)
        ->withShippingAddress(
            factory(ShippingAddress::class)->withCc($country)
        )
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier($fakeCarrier)
                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
                ->withAllShipmentOptions()
        )
        ->make();

    factory(AccountGeneralSettings::class)
        ->withHasCarrierSmallPackageContract($accountHasCarrierSmallPackageContract)
        ->store();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
    $service  = Pdk::get(PdkOrderOptionsService::class);
    $newOrder = $service->calculate($order);

    assertMatchesSnapshot((string) $newOrder->deliveryOptions->packageType);
})
    ->with('platforms')
    ->with(DESTINATION_INTERNATIONAL_COUNTRIES)
    ->with(CARRIERS)
    ->with(CONFIG);

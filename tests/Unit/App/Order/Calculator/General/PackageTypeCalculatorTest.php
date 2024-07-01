<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\Account\Model\AccountGeneralSettings;
use MyParcelNL\Pdk\Account\Platform as AccountPlatform;
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
    string $platform,
    string $country,
    bool   $carrierSetting,
    bool   $accountFlag,
    string $packageType,
    array  $carrierTests
) {
    //todo:
    // - maak voor elke accountflag/carriersetting een array aan.
    // - maak voor elke carrier een array aan.
    // flespakket hoef je niet te testen want is hetzelfde als myparcel.
    // belgie ondersteund nooit mailbox, dus dat is altijd standaard package.
    mockPlatform($platform);
    mockPdkProperties([
        'orderCalculators' => [PackageTypeCalculator::class],
    ]);

    foreach ($carrierTests as $carrierTest) {
        $fakeCarrier = factory(Carrier::class)
            ->withExternalIdentifier($carrierTest['carrierExternalIdentifier'])
            ->withCapabilities(
                factory(CarrierCapabilities::class)->fromCarrier($carrierTest['carrierName'])
            )
            ->make();

        factory(CarrierSettings::class, $fakeCarrier->externalIdentifier)
            ->withAllowInternationalMailbox($carrierSetting)
            ->store();

        $order = factory(PdkOrder::class)
            ->withShippingAddress(
                factory(ShippingAddress::class)->withCc($country)
            )
            ->withDeliveryOptions(
                factory(DeliveryOptions::class)
                    ->withCarrier($fakeCarrier)
                    ->withPackageType($packageType)
                    ->withAllShipmentOptions()
            )
            ->make();

        factory(AccountGeneralSettings::class)
            ->withHasCarrierSmallPackageContract($accountFlag)
            ->store();

        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
        $service  = Pdk::get(PdkOrderOptionsService::class);
        $newOrder = $service->calculate($order);

        expect($newOrder->deliveryOptions->packageType)->toBe($carrierTest['results'][$platform]);
    }
})
    ->with('platforms')
    ->with([
        'gb, account flag on, carrier setting on'   => [
            'country'        => CountryCodes::CC_GB,
            'carrierSetting' => true,
            'accountFlag'    => true,
            'packageType'    => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'carriers'       => [
                [
                    'carrierExternalIdentifier' => 'postnl:123',
                    'carrierName'               => 'postnl',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
                [
                    'carrierExternalIdentifier' => 'dhlforyou',
                    'carrierName'               => 'dhlforyou',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
            ],
        ],
        'gb, account flag on, carrier setting off'  => [
            'country'        => CountryCodes::CC_GB,
            'carrierSetting' => false,
            'accountFlag'    => true,
            'packageType'    => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'carriers'       => [
                [
                    'carrierExternalIdentifier' => 'postnl:123',
                    'carrierName'               => 'postnl',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
                [
                    'carrierExternalIdentifier' => 'dhlforyou',
                    'carrierName'               => 'dhlforyou',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
            ],
        ],
        'gb, account flag off, carrier setting on'  => [
            'country'        => CountryCodes::CC_GB,
            'carrierSetting' => true,
            'accountFlag'    => false,
            'packageType'    => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'carriers'       => [
                [
                    'carrierExternalIdentifier' => 'postnl:123',
                    'carrierName'               => 'postnl',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
                [
                    'carrierExternalIdentifier' => 'dhlforyou',
                    'carrierName'               => 'dhlforyou',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
            ],
        ],
        'gb, account flag off, carrier setting off' => [
            'country'        => CountryCodes::CC_GB,
            'carrierSetting' => false,
            'accountFlag'    => false,
            'packageType'    => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'carriers'       => [
                [
                    'carrierExternalIdentifier' => 'postnl:123',
                    'carrierName'               => 'postnl',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
                [
                    'carrierExternalIdentifier' => 'dhlforyou',
                    'carrierName'               => 'dhlforyou',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
            ],
        ],
        'nl, account flag on, carrier setting on'   => [
            'country'        => CountryCodes::CC_NL,
            'carrierSetting' => true,
            'accountFlag'    => true,
            'packageType'    => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'carriers'       => [
                [
                    'carrierExternalIdentifier' => 'postnl:123',
                    'carrierName'               => 'postnl',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
                [
                    'carrierExternalIdentifier' => 'dhlforyou',
                    'carrierName'               => 'dhlforyou',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
            ],
        ],
        'nl, account flag on, carrier setting off'  => [
            'country'        => CountryCodes::CC_NL,
            'carrierSetting' => false,
            'accountFlag'    => true,
            'packageType'    => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'carriers'       => [
                [
                    'carrierExternalIdentifier' => 'postnl:123',
                    'carrierName'               => 'postnl',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
                [
                    'carrierExternalIdentifier' => 'dhlforyou',
                    'carrierName'               => 'dhlforyou',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
            ],
        ],
        'nl, account flag off, carrier setting on'  => [
            'country'        => CountryCodes::CC_NL,
            'carrierSetting' => true,
            'accountFlag'    => false,
            'packageType'    => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'carriers'       => [
                [
                    'carrierExternalIdentifier' => 'postnl:123',
                    'carrierName'               => 'postnl',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
                [
                    'carrierExternalIdentifier' => 'dhlforyou',
                    'carrierName'               => 'dhlforyou',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
            ],
        ],
        'nl, account flag off, carrier setting off' => [
            'country'        => CountryCodes::CC_NL,
            'carrierSetting' => false,
            'accountFlag'    => false,
            'packageType'    => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'carriers'       => [
                [
                    'carrierExternalIdentifier' => 'postnl:123',
                    'carrierName'               => 'postnl',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
                [
                    'carrierExternalIdentifier' => 'dhlforyou',
                    'carrierName'               => 'dhlforyou',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
            ],
        ],
        'be, account flag on, carrier setting on'   => [
            'country'        => CountryCodes::CC_GB,
            'carrierSetting' => true,
            'accountFlag'    => true,
            'packageType'    => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            'carriers'       => [
                [
                    'carrierExternalIdentifier' => 'postnl:123',
                    'carrierName'               => 'postnl',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
                [
                    'carrierExternalIdentifier' => 'dhlforyou',
                    'carrierName'               => 'dhlforyou',
                    'results'                   => [
                        AccountPlatform::FLESPAKKET_NAME   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::MYPARCEL_NAME     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        AccountPlatform::SENDMYPARCEL_NAME => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    ],
                ],
            ],
        ],
    ]);

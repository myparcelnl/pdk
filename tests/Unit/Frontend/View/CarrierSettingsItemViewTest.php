<?php

/** @noinspection PhpUnhandledExceptionInspection, PhpIllegalPsrClassPathInspection, PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection, AutoloadingIssuesInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Account\Model\AccountGeneralSettings;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilitiesFactory;
use MyParcelNL\Pdk\Carrier\Model\CarrierFactory;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockCarrierSchema;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('frontend', 'settings');

function getViewSettings(CarrierFactory $carrierFactory): array
{
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockCarrierSchema $carrierSchema */
    $carrierSchema = Pdk::get(MockCarrierSchema::class);
    $carrierSchema->reset();

    $carrier = $carrierFactory->make();

    $view = new CarrierSettingsItemView($carrier);

    $array = $view->toArray(Arrayable::SKIP_NULL);

    return Arr::pluck($array['elements'], 'name');
}

usesShared(new UsesMockPdkInstance());

it('shows settings based on capabilities', function (CarrierCapabilitiesFactory $capabilitiesFactory, array $expected) {
    $emptySettings = getViewSettings(factory(Carrier::class)->withCapabilities([]));
    $settingsWithCapabilities = getViewSettings(factory(Carrier::class)->withCapabilities($capabilitiesFactory));

    expect($emptySettings)->not->toContain(...$expected)
        ->and($settingsWithCapabilities)
        ->toContain(...$expected);
})->with([
    'delivery type: standard' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withDeliveryTypes([DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME]);
        },
        [
            CarrierSettings::ALLOW_STANDARD_DELIVERY,
            CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD,
        ],
    ],

    'delivery type: morning' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withDeliveryTypes([DeliveryOptions::DELIVERY_TYPE_MORNING_NAME]);
        },
        [
            CarrierSettings::ALLOW_MORNING_DELIVERY,
            CarrierSettings::PRICE_DELIVERY_TYPE_MORNING,
        ],
    ],

    'delivery type: evening' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withDeliveryTypes([DeliveryOptions::DELIVERY_TYPE_EVENING_NAME]);
        },
        [
            CarrierSettings::ALLOW_EVENING_DELIVERY,
            CarrierSettings::PRICE_DELIVERY_TYPE_EVENING,
        ],
    ],

    'package type: mailbox' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withPackageTypes([DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME]);
        },
        [CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX],
    ],

    'package type: digital stamp' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withPackageTypes([DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME]);
        },
        [CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP],
    ],

    'package type: package_small' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withPackageTypes([DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME]);
        },
        [CarrierSettings::PRICE_PACKAGE_TYPE_PACKAGE_SMALL],
    ],

    'shipment option: only recipient' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withShipmentOptions([ShipmentOptions::ONLY_RECIPIENT => true]);
        },
        [
            CarrierSettings::EXPORT_ONLY_RECIPIENT,
            CarrierSettings::ALLOW_ONLY_RECIPIENT,
            CarrierSettings::PRICE_ONLY_RECIPIENT,
        ],
    ],

    'shipment option: signature' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withShipmentOptions([ShipmentOptions::SIGNATURE => true]);
        },
        [
            CarrierSettings::EXPORT_SIGNATURE,
            CarrierSettings::ALLOW_SIGNATURE,
            CarrierSettings::PRICE_SIGNATURE,
        ],
    ],

    'shipment option: age check' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withShipmentOptions([ShipmentOptions::AGE_CHECK => true]);
        },
        [CarrierSettings::EXPORT_AGE_CHECK],
    ],

    'shipment option: hide sender' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withShipmentOptions([ShipmentOptions::HIDE_SENDER => true]);
        },
        [CarrierSettings::EXPORT_HIDE_SENDER],
    ],

    'shipment option: direct return' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withShipmentOptions([ShipmentOptions::DIRECT_RETURN => true]);
        },
        [CarrierSettings::EXPORT_RETURN],
    ],

    'shipment option: large format' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withShipmentOptions([ShipmentOptions::LARGE_FORMAT => true]);
        },
        [
            CarrierSettings::EXPORT_LARGE_FORMAT,
            CarrierSettings::EXPORT_RETURN_LARGE_FORMAT,
        ],
    ],

    'shipment option: same day delivery' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withShipmentOptions([ShipmentOptions::SAME_DAY_DELIVERY => true]);
        },
        [
            CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
            CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY,
            CarrierSettings::CUTOFF_TIME_SAME_DAY,
        ],
    ],

    'shipment option: insurance' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withShipmentOptions([ShipmentOptions::INSURANCE => [0, 100, 1000, 10000]]);
        },
        [
            CarrierSettings::EXPORT_INSURANCE,
            CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT,
            CarrierSettings::EXPORT_INSURANCE_UP_TO,
            CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE,
            CarrierSettings::EXPORT_INSURANCE_UP_TO_EU,
            CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW,
            CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE,
        ],
    ],
]);

it(
    'shows international mailbox settings based on capabilities',
    function (
        CarrierCapabilitiesFactory $capabilitiesFactory,
        bool                       $accountHasCarrierSmallPackageContract,
        ?string                    $carrierExternalIdentifier,
        bool                       $shouldHaveInternationalMailbox
    ) {
        factory(AccountGeneralSettings::class)
            ->withHasCarrierSmallPackageContract($accountHasCarrierSmallPackageContract)
            ->store();

        $settingsWithCapabilities = getViewSettings(
            factory(Carrier::class)
                ->withCapabilities($capabilitiesFactory)
                ->withExternalIdentifier($carrierExternalIdentifier)
        );

        $internationalMailboxFields = [
            'allowInternationalMailbox',
            'priceInternationalMailbox',
        ];
        $contains                   = ! array_diff($internationalMailboxFields, $settingsWithCapabilities);

        expect($contains)->toBe($shouldHaveInternationalMailbox);
    }
)->with([
    'package type: international-mailbox, contract on, custom carrier' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withFeatures(['carrierSmallPackageContract' => CarrierSchema::FEATURE_CUSTOM_CONTRACT_ONLY])
                ->withPackageTypes([DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME]);
        },
        'accountHasCarrierSmallPackageContract' => true,
        'externalIdentifier'                    => 'postnl:12345',
        'shouldHaveInternationalMailbox'        => true,
    ],

    'package type: international-mailbox, contract off, custom carrier' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withFeatures(['carrierSmallPackageContract' => CarrierSchema::FEATURE_CUSTOM_CONTRACT_ONLY])
                ->withPackageTypes([DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME]);
        },
        'accountHasCarrierSmallPackageContract' => false,
        'externalIdentifier'                    => 'postnl:12345',
        'shouldHaveInternationalMailbox'        => true,
    ],
    'package type: international-mailbox, contract on, normal carrier'  => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withFeatures(['carrierSmallPackageContract' => CarrierSchema::FEATURE_CUSTOM_CONTRACT_ONLY])
                ->withPackageTypes([DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME]);
        },
        'accountHasCarrierSmallPackageContract' => true,
        'externalIdentifier'                    => 'postnl',
        'shouldHaveInternationalMailbox'        => true,

    ],

    'package type: international-mailbox, contract off, normal carrier' => [
        function () {
            return factory(CarrierCapabilities::class)
                ->withFeatures(['carrierSmallPackageContract' => CarrierSchema::FEATURE_CUSTOM_CONTRACT_ONLY])
                ->withPackageTypes([DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME]);
        },
        'accountHasCarrierSmallPackageContract' => false,
        'externalIdentifier'                    => 'postnl',
        'shouldHaveInternationalMailbox'        => true,

    ],
]);

it('adds afterUpdate logic to delivery options enabled toggle', function () {
    $carrier = factory(Carrier::class)->make();
    $view    = new CarrierSettingsItemView($carrier);

    $elements = $view->toArray();

    $deliveryOptionsFound = false;
    foreach ($elements['elements'] as $element) {
        if (isset($element['name']) && $element['name'] === CarrierSettings::DELIVERY_OPTIONS_ENABLED) {
            $deliveryOptionsFound = true;
            expect($element)->toHaveKey('$builders');
            break;
        }
    }

    expect($deliveryOptionsFound)->toBeTrue();
});

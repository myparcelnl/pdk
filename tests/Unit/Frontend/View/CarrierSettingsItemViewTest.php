<?php

/** @noinspection PhpUnhandledExceptionInspection, PhpIllegalPsrClassPathInspection, PhpUndefinedMethodInspection, PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection, AutoloadingIssuesInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Account\Model\AccountGeneralSettings;
use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FreshFoodDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FrozenDefinition;
use MyParcelNL\Pdk\App\Options\Definition\HideSenderDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\PriorityDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\SettingKey;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierFactory;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

uses()->group('frontend', 'settings');

function getViewSettings(CarrierFactory $carrierFactory): array
{
    $carrier = $carrierFactory->make();

    $view = new CarrierSettingsItemView($carrier);

    $array = $view->toArray(Arrayable::SKIP_NULL);

    return Arr::pluck($array['elements'], 'name');
}

usesShared(new UsesMockPdkInstance());

it('shows settings based on capabilities', function (CarrierFactory $carrierFactory, array $expected) {
    $emptySettings = getViewSettings(factory(Carrier::class));
    $settingsWithCapabilities = getViewSettings($carrierFactory);

    expect($emptySettings)->not->toContain(...$expected)
        ->and($settingsWithCapabilities)
        ->toContain(...$expected);
})->with([
    'delivery type: standard' => [
        function () {
            return factory(Carrier::class)
                ->withDeliveryTypes([RefTypesDeliveryTypeV2::STANDARD]);
        },
        [
            SettingKey::allow(RefTypesDeliveryTypeV2::STANDARD),
            SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::STANDARD),
        ],
    ],

    'delivery type: morning' => [
        function () {
            return factory(Carrier::class)
                ->withDeliveryTypes([RefTypesDeliveryTypeV2::MORNING]);
        },
        [
            SettingKey::allow(RefTypesDeliveryTypeV2::MORNING),
            SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::MORNING),
        ],
    ],

    'delivery type: evening' => [
        function () {
            return factory(Carrier::class)
                ->withDeliveryTypes([RefTypesDeliveryTypeV2::EVENING]);
        },
        [
            SettingKey::allow(RefTypesDeliveryTypeV2::EVENING),
            SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::EVENING),
        ],
    ],

    'package type: mailbox' => [
        function () {
            return factory(Carrier::class)
                ->withPackageTypes([RefShipmentPackageTypeV2::MAILBOX]);
        },
        [SettingKey::pricePackageType(RefShipmentPackageTypeV2::MAILBOX)],
    ],

    'package type: digital stamp' => [
        function () {
            return factory(Carrier::class)
                ->withPackageTypes([RefShipmentPackageTypeV2::DIGITAL_STAMP]);
        },
        [SettingKey::pricePackageType(RefShipmentPackageTypeV2::DIGITAL_STAMP)],
    ],

    'package type: package_small' => [
        function () {
            return factory(Carrier::class)
                ->withPackageTypes([RefShipmentPackageTypeV2::SMALL_PACKAGE]);
        },
        [SettingKey::pricePackageType(RefShipmentPackageTypeV2::SMALL_PACKAGE)],
    ],


    'shipment option: only recipient' => [
        function () {
            return factory(Carrier::class)
                ->withOptions(['recipientOnlyDelivery' => ['enabled' => true]]);
        },
        [
            (new OnlyRecipientDefinition())->getCarrierSettingsKey(),
            (new OnlyRecipientDefinition())->getAllowSettingsKey(),
            (new OnlyRecipientDefinition())->getPriceSettingsKey(),
        ],
    ],

    'shipment option: priority delivery' => [
        function () {
            return factory(Carrier::class)
                ->withOptions(['priorityDelivery' => ['enabled' => true]]);
        },
        [
            (new PriorityDeliveryDefinition())->getAllowSettingsKey(),
            (new PriorityDeliveryDefinition())->getPriceSettingsKey(),
        ],
    ],

    'shipment option: signature' => [
        function () {
            return factory(Carrier::class)
                ->withOptions(['requiresSignature' => ['enabled' => true]]);
        },
        [
            (new SignatureDefinition())->getCarrierSettingsKey(),
            (new SignatureDefinition())->getAllowSettingsKey(),
            (new SignatureDefinition())->getPriceSettingsKey(),
        ],
    ],

    'shipment option: age check' => [
        function () {
            return factory(Carrier::class)
                ->withOptions(['requiresAgeVerification' => ['enabled' => true]]);
        },
        [(new AgeCheckDefinition())->getCarrierSettingsKey()],
    ],

    'shipment option: hide sender' => [
        function () {
            return factory(Carrier::class)
                ->withOptions(['hideSender' => ['enabled' => true]]);
        },
        [(new HideSenderDefinition())->getCarrierSettingsKey()],
    ],

    'shipment option: direct return' => [
        function () {
            return factory(Carrier::class)
                ->withOptions(['returnOnFirstFailedDelivery' => ['enabled' => true]]);
        },
        [(new DirectReturnDefinition())->getCarrierSettingsKey()],
    ],

    'shipment option: large format' => [
        function () {
            return factory(Carrier::class)
                ->withOptions(['oversizedPackage' => ['enabled' => true]]);
        },
        [
            (new LargeFormatDefinition())->getCarrierSettingsKey(),
            CarrierSettings::EXPORT_RETURN_LARGE_FORMAT,
        ],
    ],

    'shipment option: same day delivery' => [
        function () {
            return factory(Carrier::class)
                ->withOptions(['sameDayDelivery' => ['enabled' => true]]);
        },
        [
            (new SameDayDeliveryDefinition())->getAllowSettingsKey(),
            SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::SAME_DAY),
            CarrierSettings::CUTOFF_TIME_SAME_DAY,
        ],
    ],

    'shipment option: insurance' => [
        function () {
            return factory(Carrier::class)
                ->withInsurance(0, 0, 10000);
        },
        [
            (new InsuranceDefinition())->getCarrierSettingsKey(),
            CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT,
            CarrierSettings::EXPORT_INSURANCE_UP_TO,
            CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE,
            CarrierSettings::EXPORT_INSURANCE_UP_TO_EU,
            CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW,
            CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE,
        ],
    ],
    'shipment option: fresh food' => [
        function () {
            return factory(Carrier::class)
                ->withOptions(['freshFood' => ['enabled' => true]]);
        },
        [(new FreshFoodDefinition())->getCarrierSettingsKey()],
    ],
    'shipment option: frozen' => [
        function () {
            return factory(Carrier::class)
                ->withOptions(['frozen' => ['enabled' => true]]);
        },
        [(new FrozenDefinition())->getCarrierSettingsKey()],
    ],
]);

it(
    'shows international mailbox settings based on capabilities',
    function (
        CarrierFactory $carrierFactory,
        bool           $accountHasCarrierSmallPackageContract,
        ?string        $carrier,
        bool           $shouldHaveInternationalMailbox
    ) {
        factory(AccountGeneralSettings::class)
            ->withHasCarrierSmallPackageContract($accountHasCarrierSmallPackageContract)
            ->store();

        $settingsWithFeatures = getViewSettings(
            $carrierFactory->withCarrier($carrier)
        );

        $internationalMailboxFields = [
            'allowInternationalMailbox',
            'priceInternationalMailbox',
        ];
        $contains                   = ! array_diff($internationalMailboxFields, $settingsWithFeatures);

        expect($contains)->toBe($shouldHaveInternationalMailbox);
    }
)->with([
    'package type: international-mailbox, contract on, custom carrier' => [
        function () {
            return factory(Carrier::class)
                ->withPackageTypes([RefShipmentPackageTypeV2::MAILBOX]);
        },
        'accountHasCarrierSmallPackageContract' => true,
        'carrier'                               => 'POSTNL',
        'shouldHaveInternationalMailbox'        => true,
    ],

    'package type: international-mailbox, contract off, custom carrier' => [
        function () {
            return factory(Carrier::class)
                ->withPackageTypes([RefShipmentPackageTypeV2::MAILBOX]);
        },
        'accountHasCarrierSmallPackageContract' => false,
        'carrier'                               => 'POSTNL',
        'shouldHaveInternationalMailbox'        => true,
    ],
    'package type: international-mailbox, contract on, normal carrier'  => [
        function () {
            return factory(Carrier::class)
                ->withPackageTypes([RefShipmentPackageTypeV2::MAILBOX]);
        },
        'accountHasCarrierSmallPackageContract' => true,
        'carrier'                               => 'POSTNL',
        'shouldHaveInternationalMailbox'        => true,

    ],

    'package type: international-mailbox, contract off, normal carrier' => [
        function () {
            return factory(Carrier::class)
                ->withPackageTypes([RefShipmentPackageTypeV2::MAILBOX]);
        },
        'accountHasCarrierSmallPackageContract' => false,
        'carrier'                               => 'POSTNL',
        'shouldHaveInternationalMailbox'        => true,

    ],
]);

it('marks required option form elements as readOnly', function () {
    $carrier = factory(Carrier::class)
        ->withAllCapabilities()
        ->withOptionRequired('requiresSignature')
        ->store()
        ->make();

    $view     = new CarrierSettingsItemView($carrier);
    $elements = $view->toArray()['elements'];

    $element = null;

    foreach ($elements as $el) {
        if (isset($el['name']) && $el['name'] === 'exportSignature') {
            $element = $el;
            break;
        }
    }

    expect($element)->not->toBeNull();

    $hasReadOnly = false;

    foreach ($element['$builders'] ?? [] as $builder) {
        if (array_key_exists('$readOnlyWhen', $builder)) {
            $hasReadOnly = true;
        }
    }

    expect($hasReadOnly)->toBeTrue('required option form element must be readOnly');
});

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

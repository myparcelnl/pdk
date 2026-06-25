<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Options\Definition\AgeCheckDefinition;
use MyParcelNL\Pdk\App\Options\Definition\DirectReturnDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FreshFoodDefinition;
use MyParcelNL\Pdk\App\Options\Definition\FrozenDefinition;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\App\Options\Definition\LargeFormatDefinition;
use MyParcelNL\Pdk\App\Options\Definition\OnlyRecipientDefinition;
use MyParcelNL\Pdk\App\Options\Definition\ReceiptCodeDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SameDayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SaturdayDeliveryDefinition;
use MyParcelNL\Pdk\App\Options\Definition\SignatureDefinition;
use MyParcelNL\Pdk\Base\Support\SettingKey;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\SettingsManager;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

use function DI\factory;
use function DI\value;

/**
 * Values related to settings.
 */
return [
    /**
     * Default settings that will be applied during installation.
     */

    'builtInDefaultSettings' => factory(function () {
        return [
            CarrierSettings::ID => [
                SettingsManager::KEY_ALL => [
                    // ----- Delivery types -----
                    SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_ALLOW_HOME)                                     => true, // master toggle
                    SettingKey::allow(RefTypesDeliveryTypeV2::EVENING)                       => true,
                    SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_MONDAY)               => true,
                    SettingKey::allow(RefTypesDeliveryTypeV2::MORNING)                       => true,
                    SettingKey::allow(RefTypesDeliveryTypeV2::PICKUP)                        => true,
                    (new SameDayDeliveryDefinition())->getAllowSettingsKey()                 => true,
                    (new SaturdayDeliveryDefinition())->getAllowSettingsKey()                => true,
                    SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::EVENING)            => 0,
                    SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::EXPRESS)            => 0,
                    SettingKey::priceDeliveryType(DeliveryOptions::DELIVERY_OPTION_MONDAY)    => 0,
                    SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::MORNING)            => 0,
                    SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::PICKUP)             => 0,
                    SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::SAME_DAY)           => 0,
                    SettingKey::priceDeliveryType(DeliveryOptions::DELIVERY_OPTION_SATURDAY)  => 0,
                    SettingKey::priceDeliveryType(RefTypesDeliveryTypeV2::STANDARD)           => 0,

                    // ----- Package types -----
                    SettingKey::pricePackageType(RefShipmentPackageTypeV2::DIGITAL_STAMP)     => 0,
                    SettingKey::pricePackageType(RefShipmentPackageTypeV2::MAILBOX)           => 0,

                    // ----- Shipment options -----
                    (new AgeCheckDefinition())->getCarrierSettingsKey()                       => false,
                    (new DirectReturnDefinition())->getCarrierSettingsKey()                   => false,
                    (new FreshFoodDefinition())->getCarrierSettingsKey()                      => false,
                    (new FrozenDefinition())->getCarrierSettingsKey()                         => false,
                    (new InsuranceDefinition())->getCarrierSettingsKey()                      => false,
                    CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT                             => 0,
                    CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE                        => 100,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO                                   => 0,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_EU                                => 0,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW                               => 0,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE                            => 0,
                    (new LargeFormatDefinition())->getCarrierSettingsKey()                    => false,
                    (new OnlyRecipientDefinition())->getAllowSettingsKey()                    => true,
                    (new OnlyRecipientDefinition())->getCarrierSettingsKey()                  => false,
                    (new OnlyRecipientDefinition())->getPriceSettingsKey()                    => 0,
                    (new ReceiptCodeDefinition())->getCarrierSettingsKey()                    => false,
                    CarrierSettings::EXPORT_RETURN_LARGE_FORMAT                               => false,
                    CarrierSettings::EXPORT_RETURN_PACKAGE_TYPE                               => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
                    (new SignatureDefinition())->getAllowSettingsKey()                        => true,
                    (new SignatureDefinition())->getCarrierSettingsKey()                      => false,
                    (new SignatureDefinition())->getPriceSettingsKey()                        => 0,

                    // ----- Other carrier-level settings -----
                    CarrierSettings::CUTOFF_TIME                                              => '17:00',
                    CarrierSettings::CUTOFF_TIME_SAME_DAY                                     => '09:00',
                    CarrierSettings::DEFAULT_PACKAGE_TYPE                                     => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
                    CarrierSettings::DELIVERY_DAYS_WINDOW                                     => 7,
                    CarrierSettings::DELIVERY_OPTIONS_ENABLED                                 => true,
                    CarrierSettings::DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS                  => false,
                    CarrierSettings::DIGITAL_STAMP_DEFAULT_WEIGHT                             => 0,
                    CarrierSettings::DROP_OFF_DELAY                                           => 0,
                    CarrierSettings::DROP_OFF_POSSIBILITIES                                   => [
                        'dropOffDays'           => array_map(static function (int $weekday) {
                            return [
                                'cutoffTime'        => '17:00:00',
                                'dispatch'          => true,
                                'sameDayCutoffTime' => '09:00:00',
                                'weekday'           => $weekday,
                            ];
                        }, DropOffDay::WEEKDAYS),
                        'dropOffDaysDeviations' => [],
                    ],
                ],
            ],
            OrderSettings::ID   => [
                OrderSettings::STATUS_ON_LABEL_CREATE    => TriStateService::INHERIT,
                OrderSettings::STATUS_WHEN_LABEL_SCANNED => TriStateService::INHERIT,
                OrderSettings::STATUS_WHEN_DELIVERED     => TriStateService::INHERIT,
                OrderSettings::SEND_NOTIFICATION_AFTER   => TriStateService::INHERIT,
            ],
        ];
    }),

    /**
     * Combine all default settings.
     */

    'mergedDefaultSettings' => factory(function () {
        return array_replace_recursive(
            PdkFacade::get('builtInDefaultSettings') ?? [],
            [
                CustomsSettings::ID => [
                    CustomsSettings::COUNTRY_OF_ORIGIN => Pdk::get(PropositionService::class)->getPropositionConfig()->countryCode
                ],
            ],
            PdkFacade::get('defaultSettings') ?? []
        );
    }),

    /**
     * Settings that are disabled and not shown.
     */

    'disabledSettings' => value([]),

    /**
     * Prefix for all settings keys.
     */

    'settingKeyPrefix' => factory(function () {
        return sprintf('%s_', PdkFacade::getAppInfo()->name);
    }),

    /**
     * Callback to generate a settings key.
     */

    'createSettingsKey' => factory(function () {
        return static function (string $key) {
            return sprintf('%s%s', PdkFacade::get('settingKeyPrefix'), $key);
        };
    }),

    /**
     * Callback to generate a barcode note identifier
     */

    'createBarcodeNoteIdentifier' => factory(function () {
        return static function (string $shipmentId) {
            return "barcode-$shipmentId";
        };
    }),

    /**
     * Settings key where the installed version of the plugin is saved.
     */

    'settingKeyVersion' => factory(function () {
        return PdkFacade::get('createSettingsKey')('version');
    }),

    /**
     * Settings key where webhooks are saved
     */

    'settingKeyWebhooks' => factory(function () {
        return PdkFacade::get('createSettingsKey')('webhooks');
    }),

    /**
     * Settings key where the full webhook url is saved
     */

    'settingKeyWebhookHash' => factory(function () {
        return PdkFacade::get('createSettingsKey')('webhook_hash');
    }),

    /**
     * Settings key where the installed version of the app is saved.
     */

    'settingKeyInstalledVersion' => factory(function () {
        return PdkFacade::get('createSettingsKey')('installed_version');
    }),

    /**
     * Settings key where the list of applied migration identities is saved.
     * Format: string[] of migration ids (FQCN for class-based, filename for file-based).
     */
    'settingKeyAppliedMigrations' => factory(function () {
        return PdkFacade::get('createSettingsKey')('applied_migrations');
    }),

    /**
     * Directory the installer scans for timestamped migration files.
     * Defaults to "<rootDir>/src/Migration". Plugins can override in their
     * own config to point at a different directory, or set to null to disable
     * PDK-owned discovery entirely (e.g. if the plugin prefers to register
     * every source explicitly via its MigrationService).
     */
    'migrationDirectory' => factory(function () {
        return rtrim(PdkFacade::get('rootDir'), '/') . '/src/Migration';
    }),
];

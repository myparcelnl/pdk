<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\SettingsManager;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;
use function DI\factory;

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
                    CarrierSettings::ALLOW_DELIVERY_OPTIONS                  => true,
                    CarrierSettings::ALLOW_EVENING_DELIVERY                  => true,
                    CarrierSettings::ALLOW_MONDAY_DELIVERY                   => true,
                    CarrierSettings::ALLOW_MORNING_DELIVERY                  => true,
                    CarrierSettings::ALLOW_ONLY_RECIPIENT                    => true,
                    CarrierSettings::ALLOW_PICKUP_LOCATIONS                  => true,
                    CarrierSettings::ALLOW_SAME_DAY_DELIVERY                 => true,
                    CarrierSettings::ALLOW_SATURDAY_DELIVERY                 => true,
                    CarrierSettings::ALLOW_SIGNATURE                         => true,
                    CarrierSettings::CUTOFF_TIME                             => '17:00',
                    CarrierSettings::CUTOFF_TIME_SAME_DAY                    => '09:00',
                    CarrierSettings::DEFAULT_PACKAGE_TYPE                    => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
                    CarrierSettings::DELIVERY_DAYS_WINDOW                    => 7,
                    CarrierSettings::DELIVERY_OPTIONS_ENABLED                => true,
                    CarrierSettings::DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS => false,
                    CarrierSettings::DIGITAL_STAMP_DEFAULT_WEIGHT            => 0,
                    CarrierSettings::DROP_OFF_DELAY                          => 0,
                    CarrierSettings::DROP_OFF_POSSIBILITIES                  => [
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
                    CarrierSettings::EXPORT_AGE_CHECK                        => false,
                    CarrierSettings::EXPORT_INSURANCE                        => false,
                    CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT            => 0,
                    CarrierSettings::EXPORT_INSURANCE_PRICE_PERCENTAGE       => 100,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO                  => 0,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_EU               => 0,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_ROW              => 0,
                    CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE           => 0,
                    CarrierSettings::EXPORT_LARGE_FORMAT                     => false,
                    CarrierSettings::EXPORT_ONLY_RECIPIENT                   => false,
                    CarrierSettings::EXPORT_RETURN                           => false,
                    CarrierSettings::EXPORT_RETURN_LARGE_FORMAT              => false,
                    CarrierSettings::EXPORT_RETURN_PACKAGE_TYPE              => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
                    CarrierSettings::EXPORT_SIGNATURE                        => false,
                    CarrierSettings::PRICE_DELIVERY_TYPE_EVENING             => 0,
                    CarrierSettings::PRICE_DELIVERY_TYPE_MONDAY              => 0,
                    CarrierSettings::PRICE_DELIVERY_TYPE_MORNING             => 0,
                    CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP              => 0,
                    CarrierSettings::PRICE_DELIVERY_TYPE_SAME_DAY            => 0,
                    CarrierSettings::PRICE_DELIVERY_TYPE_SATURDAY            => 0,
                    CarrierSettings::PRICE_DELIVERY_TYPE_STANDARD            => 0,
                    CarrierSettings::PRICE_ONLY_RECIPIENT                    => 0,
                    CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP        => 0,
                    CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX              => 0,
                    CarrierSettings::PRICE_SIGNATURE                         => 0,
                    CarrierSettings::SHOW_DELIVERY_DAY                       => true,
                ],
            ],
        ];
    }),

    /**
     * Combine all default settings.
     */

    'mergedDefaultSettings' => factory(function () {
        return array_replace_recursive(
            PdkFacade::get('builtInDefaultSettings') ?? [],
            Platform::get('defaultSettings') ?? [],
            PdkFacade::get('defaultSettings') ?? []
        );
    }),

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
];

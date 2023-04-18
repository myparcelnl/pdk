<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use function DI\env;
use function DI\factory;
use function DI\value;

/**
 * Default config values.
 */
return [
    /**
     * Path to the root directory of the pdk.
     */
    'rootDir'                => value(__DIR__ . '/../'),

    /**
     * Mode to use for the PDK. Defaults to production. Set to debug to show debug messages and stack traces in exceptions.
     */
    'mode'                   => env('PDK_MODE', Pdk::MODE_PRODUCTION),

    /**
     * Url to the API.
     */
    'apiUrl'                 => env('PDK_API_URL', 'https://api.myparcel.nl'),

    /**
     * CDN URL to use for frontend dependencies.
     */
    'baseCdnUrl'             => value('https://cdnjs.cloudflare.com/ajax/libs/:name/:version/:filename'),

    /**
     * The minimum PHP version required to run the app.
     */
    'minimumPhpVersion'      => value('7.1'),

    /**
     * The version of the delivery options in the checkout.
     *
     * @see https://github.com/myparcelnl/delivery-options/releases
     */
    'deliveryOptionsVersion' => value('5.7.1'),

    /**
     * The default time zone to use for date and time functions.
     */
    'defaultTimeZone'        => value('Europe/Amsterdam'),

    /**
     * Carriers that can be used and shown. Only use carriers that we tested and have a schema for, at the moment
     */
    'allowedCarriers'        => value([
        'dhleuroplus',
        'dhlforyou',
        'dhlparcelconnect',
        'postnl',
        // todo: bpost
        // todo: dpd
    ]),

    'carriersWithTaxFields'       => value([
        'dhleuroplus',
    ]),

    /**
     * Countries that support split address fields.
     */
    'splitAddressFieldsCountries' => value([
        CountryCodes::CC_NL,
        CountryCodes::CC_BE,
    ]),

    /**
     * The name of the hidden input in the checkout where delivery options are stored.
     */

    'checkoutHiddenInputName' => factory(function () {
        return sprintf('%s_checkout_data', PdkFacade::getAppInfo()->name);
    }),

    /**
     * Settings
     */

    'defaultSettings' => value([]),

    'settingKeyPrefix' => factory(function () {
        return sprintf('%s_', PdkFacade::getAppInfo()->name);
    }),

    'createSettingsKey' => factory(function () {
        return static function (string $key) {
            return sprintf('%s%s', PdkFacade::get('settingKeyPrefix'), $key);
        };
    }),

    /** Settings key where the installed version of the plugin is saved. */

    'settingKeyVersion' => factory(function () {
        return PdkFacade::get('createSettingsKey')('version');
    }),

    /** Settings key where webhooks are saved */

    'settingKeyWebhooks' => factory(function () {
        return PdkFacade::get('createSettingsKey')('webhooks');
    }),

    /** Settings key where the hashed webhook url is saved */

    'settingKeyWebhookHash' => factory(function () {
        return PdkFacade::get('createSettingsKey')('webhook_hash');
    }),

    'settingKeyInstalledVersion' => factory(function () {
        return PdkFacade::get('createSettingsKey')('installed_version');
    }),

    'dropOffDelayMinimum' => value(0),
    'dropOffDelayMaximum' => value(14),

    'customsCodeMaxLength' => value(10),
];

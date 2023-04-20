<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use function DI\factory;
use function DI\value;

/**
 * Values related to settings.
 */
return [
    /**
     * Default settings that will be applied during installation.
     */

    'defaultSettings' => value([]),

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

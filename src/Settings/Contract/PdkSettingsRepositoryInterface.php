<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Contract;

use MyParcelNL\Pdk\Base\Contract\RepositoryInterface;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel;
use MyParcelNL\Pdk\Settings\Model\Settings;

interface PdkSettingsRepositoryInterface extends RepositoryInterface
{
    /**
     * Retrieve all settings from your platform.
     */
    public function all(): Settings;

    /**
     * Get a single setting's value from your platform by a dot separated setting identifier.
     *
     * @see     \MyParcelNL\Pdk\Settings\Model\Settings
     * @example get('general.apiKey')
     * @example get('carrier.postnl.allowOnlyRecipient')
     */
    public function get(string $key);

    /**
     * Store a single setting's value in your platform by a dot separated setting identifier.
     */
    public function store(string $key, $value): void;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\Settings $settings
     *
     * @return void
     */
    public function storeAllSettings(Settings $settings): void;

    /**
     * @param  SettingsModelCollection|AbstractSettingsModel $settings
     *
     * @return void
     */
    public function storeSettings($settings): void;
}

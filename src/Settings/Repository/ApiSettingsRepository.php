<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Settings\Model\Settings;

abstract class ApiSettingsRepository extends ApiRepository
{
    /**
     * Retrieve existing settings from your platform.
     *
     * @return \MyParcelNL\Pdk\Settings\Model\Settings
     */
    abstract public function getSettings(): Settings;

    /**
     * Save given settings in your platform.
     *
     * @param  \MyParcelNL\Pdk\Settings\Model\Settings $settings
     *
     * @return void
     */
    abstract public function store(Settings $settings): void;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Contract;

use MyParcelNL\Pdk\Settings\Model\Settings;

interface SettingsManagerInterface
{
    /**
     * @return \MyParcelNL\Pdk\Settings\Model\Settings
     */
    public function all(): Settings;

    /**
     * @param  string      $key
     * @param  null|string $namespace
     * @param  mixed       $default
     *
     * @return mixed
     */
    public function get(string $key, ?string $namespace = null, $default = null);

    /**
     * @return array
     * @noinspection PhpUnused
     */
    public function getDefaults(): array;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Contract;

use MyParcelNL\Pdk\Settings\Model\Settings;

interface SettingsManagerInterface
{
    public function all(): Settings;

    /**
     * @param  null|string $namespace
     *
     * @return mixed
     */
    public function get(string $key, ?string $namespace = null, mixed $default = null);

    /**
     * @noinspection PhpUnused
     */
    public function getDefaults(): array;
}

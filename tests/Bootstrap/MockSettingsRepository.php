<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;

class MockSettingsRepository extends AbstractSettingsRepository
{
    private array $settings = [];

    /**
     * @return mixed
     */
    public function getGroup(string $namespace)
    {
        return Arr::get($this->settings, $namespace, []);
    }

    public function reset(): void
    {
        $this->settings = [];
    }

    /**
     * @param  mixed $value
     */
    public function store(string $key, $value): void
    {
        Arr::set($this->settings, $key, $value);
        $this->save($key, $value);
    }
}

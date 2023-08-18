<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Contract\MockServiceInterface;

final class MockSettingsRepository extends AbstractSettingsRepository implements MockServiceInterface
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * @param  string $namespace
     *
     * @return mixed
     */
    public function getGroup(string $namespace)
    {
        return Arr::get($this->settings, $namespace, []);
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->settings = [];
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function store(string $key, $value): void
    {
        Arr::set($this->settings, $key, $value);
        $this->save($key, $value);
    }
}

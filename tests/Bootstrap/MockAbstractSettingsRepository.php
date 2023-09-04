<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use MyParcelNL\Pdk\Storage\MemoryCacheStorageDriver;

class MockAbstractSettingsRepository extends AbstractSettingsRepository
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * @param  array                                            $settings
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorageDriver $storage
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(array $settings = [], MemoryCacheStorageDriver $storage)
    {
        parent::__construct($storage);

        foreach ($settings as $key => $value) {
            $this->store($this->createSettingsKey($key), $value);
        }
    }

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

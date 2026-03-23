<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Repository\AbstractPdkSettingsRepository;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;

class MockSettingsRepository extends AbstractPdkSettingsRepository
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * @param  array                                           $settings
     * @param  null|\MyParcelNL\Pdk\Storage\MemoryCacheStorage $storage
     */
    public function __construct(array $settings = [], ?MemoryCacheStorage $storage = null)
    {
        $storage = $storage ?? new MemoryCacheStorage();

        parent::__construct($storage);
        $this->reset();

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
        /** @var string $installedVersionKey */
        $installedVersionKey = Pdk::get('settingKeyInstalledVersion');

        $this->settings = [
            $installedVersionKey => null,
        ];
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

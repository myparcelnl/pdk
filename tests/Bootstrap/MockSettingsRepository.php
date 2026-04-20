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
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage $storage
     * @param  array                                      $settings
     */
    public function __construct(MemoryCacheStorage $storage, array $settings = [])
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

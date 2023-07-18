<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;

class MockSettingsRepository extends AbstractSettingsRepository
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * @param  array                                      $settings
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage $storage
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(array $settings = [], MemoryCacheStorage $storage)
    {
        foreach ($settings as $key => $value) {
            $resolvedKey                  = Pdk::get('createSettingsKey')($key);
            $this->settings[$resolvedKey] = $value;
        }

        parent::__construct($storage);
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

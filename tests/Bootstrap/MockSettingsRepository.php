<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;

class MockSettingsRepository extends AbstractSettingsRepository
{
    /**
     * @var \MyParcelNL\Pdk\Settings\Model\Settings
     */
    private $settings;

    /**
     * @param  array                                           $settings
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage      $storage
     * @param  \MyParcelNL\Pdk\Api\Service\ApiServiceInterface $api
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(array $settings = [], MemoryCacheStorage $storage, ApiServiceInterface $api)
    {
        $this->settings = new Settings($settings);

        parent::__construct($storage, $api);
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function store(string $key, $value): void
    {
        $array = $this->settings->toArray();

        Arr::set($array, $key, $value);

        $this->settings = new Settings($array);
    }
}

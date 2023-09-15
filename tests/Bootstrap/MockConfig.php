<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;

final class MockConfig extends Config
{
    public const SUBSCRIPTION_ID_DHL_FOR_YOU = 23182;

    private readonly array $config;

    public function __construct(FileSystemInterface $fileSystem, array $data = [])
    {
        parent::__construct($fileSystem);

        $carriers = $this->getFromRealConfig('carriers');

        $this->config = array_replace_recursive(['carriers' => $carriers], $data);
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        if (! Arr::has($this->config, $key)) {
            return $this->getFromRealConfig($key);
        }

        return Arr::get($this->config, $key);
    }

    /**
     * @return mixed
     */
    private function getFromRealConfig(string $key)
    {
        /** @var \MyParcelNL\Pdk\Base\Config $config */
        $config = Pdk::get(Config::class);

        return $config->get($key);
    }
}

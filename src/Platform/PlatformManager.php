<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Platform;

use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;

class PlatformManager implements PlatformManagerInterface
{
    public function all(): array
    {
        return Config::get(sprintf('platform/%s', $this->getPlatform()));
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        return Config::get(sprintf('platform/%s.%s', $this->getPlatform(), $key));
    }

    public function getCarriers(): CarrierCollection
    {
        return Pdk::get('carriers');
    }

    public function getPlatform(): string
    {
        return (string) Pdk::get('platform');
    }
}

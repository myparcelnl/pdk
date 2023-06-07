<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Platform;

use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;

class PlatformManager implements PlatformManagerInterface
{
    /**
     * @return array
     */
    public function all(): array
    {
        return Config::get(sprintf("platform/%s", $this->getPlatform()));
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return Config::get(sprintf("platform/%s.%s", $this->getPlatform(), $key));
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return (string) Pdk::get('platform');
    }
}

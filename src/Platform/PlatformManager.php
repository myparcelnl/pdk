<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Platform;

use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;

class PlatformManager
{
    /**
     * @var string
     */
    private $platform;

    public function __construct()
    {
        $this->platform = Pdk::get('platform');
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return Config::get("platform/$this->platform");
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return Config::get("platform/$this->platform.$key");
    }
}

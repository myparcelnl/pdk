<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Platform;

interface PlatformManagerInterface
{
    /**
     * @return array
     */
    public function all(): array;

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * @return string
     */
    public function getPlatform(): string;
}

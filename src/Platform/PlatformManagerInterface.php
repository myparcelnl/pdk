<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Platform;

interface PlatformManagerInterface
{
    public function all(): array;

    /**
     * @return mixed
     */
    public function get(string $key);

    public function getPlatform(): string;
}

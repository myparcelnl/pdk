<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Concern;

use MyParcelNL\Pdk\Base\Model\AppInfo;

interface PdkInterface
{
    public function get(string $key);

    public function getAppInfo(): AppInfo;

    public function getMode(): string;

    public function has(string $key): bool;

    public function isDevelopment(): bool;

    public function isProduction(): bool;
}

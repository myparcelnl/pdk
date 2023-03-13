<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Contract;

interface ScriptServiceInterface
{
    public function createCdnUrl(string $name, string $version, string $filename): string;

    public function getBaseCdnUrl(): string;
}

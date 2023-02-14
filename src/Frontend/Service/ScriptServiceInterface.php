<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

interface ScriptServiceInterface
{
    public function createCdnUrl(string $name, string $version, string $filename): string;

    public function getBaseCdnUrl(): string;
}

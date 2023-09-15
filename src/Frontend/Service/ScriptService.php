<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Contract\ScriptServiceInterface;

class ScriptService implements ScriptServiceInterface
{
    public function createCdnUrl(string $name, string $version, string $filename): string
    {
        return strtr($this->getBaseCdnUrl(), [
            ':name'     => $name,
            ':version'  => $version,
            ':filename' => $filename,
        ]);
    }

    public function getBaseCdnUrl(): string
    {
        return Pdk::get('baseCdnUrl');
    }
}

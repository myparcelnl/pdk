<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use MyParcelNL\Pdk\Facade\Pdk;

class ScriptService implements ScriptServiceInterface
{
    /**
     * @param  string $name
     * @param  string $version
     * @param  string $filename
     *
     * @return string
     */
    public function createCdnUrl(string $name, string $version, string $filename): string
    {
        return strtr($this->getBaseCdnUrl(), [
            ':name'     => $name,
            ':version'  => $version,
            ':filename' => $filename,
        ]);
    }

    /**
     * @return string
     */
    public function getBaseCdnUrl(): string
    {
        return Pdk::get('baseCdnUrl');
    }
}

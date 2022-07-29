<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use InvalidArgumentException;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;

class Config implements ConfigInterface
{
    private static $cache = [];

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        $pathParts = [];

        if (Str::contains($key, '.')) {
            $pathParts = explode('.', $key);
            $filename  = $pathParts[0];
        } else {
            $filename = $key;
        }

        $data = $this->getConfigFile($filename);

        if (count($pathParts)) {
            array_shift($pathParts);
            return Arr::get($data, implode('.', $pathParts));
        }

        return $data;
    }

    /**
     * @param  string $filename
     *
     * @return mixed
     */
    private function getConfigFile(string $filename)
    {
        if (! isset(self::$cache[$filename])) {
            $path = sprintf('%s/../../config/%s.php', __DIR__, $filename);

            if (! file_exists($path)) {
                throw new InvalidArgumentException(sprintf('File config/%s.php does not exist.', $filename));
            }

            self::$cache[$filename] = require $path;
        }

        return self::$cache[$filename];
    }
}

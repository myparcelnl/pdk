<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use InvalidArgumentException;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;

class Config
{
    private static $cache = [];

    /**
     * @param  string $name
     *
     * @return mixed
     */
    public static function get(string $name)
    {
        $pathParts = [];

        if (Str::contains($name, '.')) {
            $pathParts = explode('.', $name);
            $filename  = $pathParts[0];
        } else {
            $filename = $name;
        }

        $data = self::getConfigFile($filename);

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
    private static function getConfigFile(string $filename)
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

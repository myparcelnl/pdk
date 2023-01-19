<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Support\Arr;
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

        $data = $this->findConfig($filename);

        if (count($pathParts)) {
            array_shift($pathParts);
            return Arr::get($data, implode('.', $pathParts));
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getConfigDir(): string
    {
        return sprintf('%s/../../config', __DIR__);
    }

    /**
     * @param  string $filename
     *
     * @return mixed
     */
    private function findConfig(string $filename)
    {
        if (! isset(self::$cache[$filename])) {
            $dir = $this->getConfigDir();

            $phpPath  = "$dir/$filename.php";
            $jsonPath = "$dir/$filename.json";

            $isPhpFile  = file_exists($phpPath);
            $isJsonFile = file_exists($jsonPath);

            if (! $isPhpFile && ! $isJsonFile) {
                throw new InvalidArgumentException(sprintf('File config/%s.php does not exist.', $filename));
            }

            if ($isJsonFile) {
                self::$cache[$filename] = json_decode(file_get_contents($jsonPath), true);
            }

            if ($isPhpFile) {
                self::$cache[$filename] = require $phpPath;
            }
        }

        return self::$cache[$filename];
    }
}

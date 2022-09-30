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
     * Get a key from the config. Separate by forward slashes to traverse directories and files, and separate by dots
     * to select specific values in the resolved file. Example: Config::get('')
     *
     * @example Config::get('platform/myparcel.human'); // MyParcel
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        [$pathParts, $filename] = $this->parseKey($key);

        $data = $this->getConfigFile($filename);

        if ($pathParts && count($pathParts)) {
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
     * @param  string $key
     *
     * @return array
     */
    protected function parseKey(string $key): array
    {
        if (Str::contains($key, '.')) {
            $pathParts = explode('.', $key);
            $filename  = $pathParts[0];

            return [$pathParts, $filename];
        }

        return [null, $key];
    }

    /**
     * @param  string $filename
     *
     * @return mixed
     */
    private function getConfigFile(string $filename)
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

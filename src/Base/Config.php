<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use MyParcelNL\Sdk\src\Support\Str;

class Config implements ConfigInterface
{
    private static $cache = [];

    /**
     * @var \MyParcelNL\Pdk\Base\FileSystemInterface
     */
    protected $fileSystem;

    /**
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface $fileSystem
     */
    public function __construct(FileSystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->fileSystem->isFile($key) || $this->fileSystem->isDir($key)
            ? $this->loadFileCached($key)
            : $this->getFileByKey($key);
    }

    /**
     * @return string[]
     */
    protected function getConfigDirs(): array
    {
        return PdkFacade::get('configDirs');
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    protected function getFileByKey(string $key)
    {
        $pathParts = [];

        if (Str::contains($key, '.')) {
            $pathParts = explode('.', $key);
            $filename  = $pathParts[0];
        } else {
            $filename = $key;
        }

        $data = $this->loadFileCached($filename);

        if (count($pathParts)) {
            array_shift($pathParts);
            return Arr::get($data, implode('.', $pathParts));
        }

        return $data;
    }

    /**
     * @return array[]
     */
    protected function getFilenameParserMap(): array
    {
        return [
            '.php' => [$this, 'parsePhp'],
            '.inc' => [$this, 'parsePhp'],
            '.json' => [$this, 'parseJson'],
        ];
    }

    /**
     * @param  string $filename
     *
     * @return null|mixed
     * @noinspection MultipleReturnStatementsInspection
     */
    protected function load(string $filename)
    {
        if ($this->fileSystem->isDir($filename)) {
            return $this->loadDirectory($filename);
        }

        if ($this->fileSystem->isFile($filename)) {
            return $this->loadFile($filename);
        }

        return null;
    }

    /**
     * @param  string $filename
     *
     * @return mixed
     */
    protected function loadByKey(string $filename)
    {
        $paths = [];

        foreach ($this->getConfigDirs() as $dir) {
            $paths[] = "$dir/$filename";

            foreach (array_keys($this->getFilenameParserMap()) as $extension) {
                $paths[] = "$dir/$filename$extension";
            }
        }

        foreach ($paths as $path) {
            $content = $this->load($path);

            if ($content) {
                return $content;
            }
        }

        throw new InvalidArgumentException(sprintf('File "%s" not found.', $filename));
    }

    /**
     * @param  string $path
     *
     * @return array
     */
    protected function loadDirectory(string $path): array
    {
        $files = array_filter($this->fileSystem->scandir($path), static function ($file) {
            return ! in_array($file, ['.', '..']);
        });

        return array_combine(
            $files,
            array_map(function ($file) use ($path) {
                return $this->loadFileCached("$path/$file");
            }, $files)
        );
    }

    /**
     * @param  string $path
     *
     * @return mixed
     */
    protected function loadFile(string $path)
    {
        $map = $this->getFilenameParserMap();

        $parser = array_reduce(
            array_keys($map),
            static function ($carry, $extension) use ($map, $path) {
                return Str::endsWith($path, $extension) ? $map[$extension] : $carry;
            }
        );

        return $parser ? $parser($path) : null;
    }

    /**
     * @param  string $filename
     *
     * @return mixed
     */
    protected function loadFileCached(string $filename)
    {
        if (! isset(self::$cache[$filename])) {
            self::$cache[$filename] = $this->load($filename) ?? $this->loadByKey($filename);
        }

        return self::$cache[$filename];
    }

    /**
     * @param  string $filename
     *
     * @return array
     */
    protected function parseJson(string $filename): array
    {
        return json_decode($this->fileSystem->get($filename), true);
    }

    /**
     * @param  string $filename
     *
     * @return array
     */
    protected function parsePhp(string $filename): array
    {
        return require $filename;
    }
}

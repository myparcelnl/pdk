<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\FileSystem;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class MockFileSystem implements FileSystemInterface
{
    private const DIRECTORY_TOKEN = '[DIR]';

    /**
     * @var Collection|array<string,string>
     */
    private static $files;

    public function __construct()
    {
        $this->setupFiles();
    }

    /**
     * @param  resource $stream
     *
     * @return void
     */
    public function closeStream($stream): void
    {
        fclose($stream);
    }

    /**
     * @param  string $file
     *
     * @return string
     */
    public function dirname(string $file): string
    {
        $parts = explode('/', $file);

        return implode('/', array_slice($parts, 0, -1));
    }

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return self::$files->has($this->resolvePath($path));
    }

    /**
     * @param  string $path
     *
     * @return string
     */
    public function get(string $path): string
    {
        if (! $this->fileExists($path)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist.', $this->resolvePath($path)));
        }

        return self::$files->get($this->resolvePath($path));
    }

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function isDir(string $path): bool
    {
        return self::DIRECTORY_TOKEN === self::$files->get($this->resolvePath($path));
    }

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function isFile(string $path): bool
    {
        return $this->fileExists($path) && ! $this->isDir($path);
    }

    /**
     * @param  string $path
     * @param  bool   $recursive
     *
     * @return void
     */
    public function mkdir(string $path, bool $recursive = false): void
    {
        $dirname = $this->dirname($path);

        if ($recursive && $path && ! $this->isDir($dirname)) {
            $this->mkdir($dirname, true);
            return;
        }

        self::$files->put($this->resolvePath($path), self::DIRECTORY_TOKEN);
    }

    /**
     * @param  string $path
     * @param  string $mode
     *
     * @return resource
     */
    public function openStream(string $path, string $mode)
    {
        return fopen('php://memory', 'wb+');
    }

    /**
     * @param  string $path
     * @param  string $contents
     *
     * @return void
     */
    public function put(string $path, string $contents): void
    {
        $this->mkdir($this->dirname($path), true);

        self::$files->put($this->resolvePath($path), $contents);
    }

    /**
     * @param  string $path
     *
     * @return string
     */
    public function realpath(string $path): string
    {
        return $this->resolvePath($path);
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        self::$files = new Collection();
    }

    /**
     * @param  string $path
     *
     * @return array
     */
    public function scandir(string $path): array
    {
        return self::$files->keys()
            ->reduce(function (array $carry, string $key) use ($path): array {
                if ($this->dirname($key) === $path) {
                    $carry[] = $this->basename($key);
                }

                return $carry;
            }, ['..', '.']);
    }

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function unlink(string $path): bool
    {
        self::$files->forget($this->resolvePath($path));

        return true;
    }

    /**
     * @param  resource $stream
     * @param  string   $contents
     *
     * @return void
     */
    public function writeToStream($stream, string $contents): void
    {
        fwrite($stream, $contents);
    }

    /**
     * @param  string $key
     *
     * @return string
     */
    private function basename(string $key): string
    {
        $parts = explode('/', $key);

        return end($parts);
    }

    /**
     * @param  string $configDir
     *
     * @return void
     */
    private function copyRealDirectory(string $configDir): void
    {
        /** @var \MyParcelNL\Pdk\Base\FileSystem $realFileSystem */
        $realFileSystem = Pdk::get(FileSystem::class);

        $dirIterator = new RecursiveDirectoryIterator($configDir);

        foreach (new RecursiveIteratorIterator($dirIterator) as $file) {
            if (! $file->isFile()) {
                $this->mkdir($file->getRealPath());
                continue;
            }

            $pathname = $file->getRealPath();

            $this->put($pathname, $realFileSystem->get($pathname));
        }
    }

    /**
     * Files and directories that should be copied from the real file system.
     *
     * @return string[]
     */
    private function getRealPaths(): array
    {
        return array_merge(
            Pdk::get('configDirs'),
            [
                __DIR__ . '/../../composer.json',
                __DIR__ . '/../../src/Frontend/Template',
            ]
        );
    }

    /**
     * Resolve a path, supporting /../ etc
     *
     * @param  string $path
     *
     * @return string
     */
    private function resolvePath(string $path): string
    {
        $trimmedPath = preg_replace('/\/+/', '/', $path);

        $parts = explode('/', $trimmedPath);
        $newParts = [];

        foreach ($parts as $part) {
            if ('..' === $part) {
                array_pop($newParts);
            } else {
                $newParts[] = $part;
            }
        }

        return implode('/', $newParts);
    }

    /**
     * Copy all real files in the config directory to the fake file system.
     *
     * @return void
     */
    private function setupFiles(): void
    {
        if (self::$files && self::$files->isNotEmpty()) {
            return;
        }

        $this->reset();

        /** @var \MyParcelNL\Pdk\Base\FileSystem $realFileSystem */
        $realFileSystem = Pdk::get(FileSystem::class);

        foreach ($this->getRealPaths() as $path) {
            if (! $realFileSystem->isDir($path)) {
                $this->put($path, $realFileSystem->get($path));
                continue;
            }

            $this->mkdir($path);
            $this->copyRealDirectory($path);
        }
    }
}

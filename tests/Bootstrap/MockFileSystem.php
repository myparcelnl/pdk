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

    /**
     * Resolved paths whose next exclusive create must fail. Lets a test reproduce the create
     * race, where a competing process holds the lock at create time and releases it before the
     * loser gets to look at the file.
     *
     * @var string[]
     */
    private static $failExclusiveCreateOnce = [];

    /**
     * Modification time per resolved path. A real filesystem keeps this for us; the mock has to
     * record it so code that dates a file by its mtime is testable.
     *
     * @var array<string,int>
     */
    private static $mtimes = [];

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
     * @return null|resource
     */
    public function openStream(string $path, string $mode)
    {
        // Mode "x" is an exclusive create: it fails when the file already exists. That is what
        // makes it usable as a lock, so the mock has to model it. Every other mode keeps the
        // previous behaviour and hands back a scratch stream.
        if (0 === strpos($mode, 'x')) {
            $queued = array_search($this->resolvePath($path), self::$failExclusiveCreateOnce, true);

            if (false !== $queued) {
                // Stands in for losing the create race to another process. The file is absent
                // again by the time the caller looks, which no real sequence of mock calls can
                // reproduce.
                unset(self::$failExclusiveCreateOnce[$queued]);

                return null;
            }

            if ($this->fileExists($path)) {
                return null;
            }

            $this->put($path, '');
        }

        return fopen('php://memory', 'wb+');
    }

    /**
     * Makes the next exclusive create of this path fail without leaving a file behind.
     *
     * @param  string $path
     *
     * @return void
     */
    public static function failNextExclusiveCreate(string $path): void
    {
        self::$failExclusiveCreateOnce[] = $path;
    }

    /**
     * Drops any queued failure that was never consumed, so it cannot leak into a later test.
     *
     * @return void
     */
    public static function clearExclusiveCreateFailures(): void
    {
        self::$failExclusiveCreateOnce = [];
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

        $resolved = $this->resolvePath($path);

        self::$files->put($resolved, $contents);
        self::$mtimes[$resolved] = time();
    }

    /**
     * @param  string $path
     *
     * @return null|int
     */
    public function mtime(string $path): ?int
    {
        return self::$mtimes[$this->resolvePath($path)] ?? null;
    }

    /**
     * Backdates a file, so a test can age it past a timeout without waiting.
     *
     * @param  string $path
     * @param  int    $timestamp
     *
     * @return void
     */
    public static function setMtime(string $path, int $timestamp): void
    {
        self::$mtimes[$path] = $timestamp;
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
        self::$files  = new Collection();
        self::$mtimes = [];
        // Deliberately not clearing failExclusiveCreateOnce here: reset() also runs from the
        // constructor, which would wipe a queued failure before the code under test reaches it.
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
        $resolved = $this->resolvePath($path);

        self::$files->forget($resolved);
        unset(self::$mtimes[$resolved]);

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

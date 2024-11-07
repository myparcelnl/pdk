<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Concern\HasCommandContext;
use MyParcelNL\Pdk\Console\Types\Shared\Concern\ReportsTiming;
use MyParcelNL\Pdk\Console\Types\Shared\Service\ParsesPhpDocs;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Base\Support\Str;
use Nette\Loaders\RobotLoader;

final class PhpLoader
{
    use HasCommandContext;
    use ReportsTiming;
    use ParsesPhpDocs;

    /**
     * @var \MyParcelNL\Pdk\Base\FileSystemInterface
     */
    private $fileSystem;

    /**
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface $fileSystem
     */
    public function __construct(FileSystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * @param  array $input
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function load(array $input): Collection
    {
        $partitioned = $this->partitionFiles($input);

        /** @var Collection $directories */
        $directories = $partitioned->first();

        /** @var Collection $files */
        $files = $partitioned->last();

        $classes = $this->loadFiles($directories->merge($files));

        return $classes
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $paths
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    private function loadFiles(Collection $paths): Collection
    {
        $loader = new RobotLoader();

        $paths->each(function (string $path) use ($loader) {
            $loader->addDirectory($path);
        });

        // Scans directories for classes / interfaces / traits
        $loader->rebuild();

        return new Collection(array_keys($loader->getIndexedClasses()));
    }

    /**
     * @param  array $files
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    private function partitionFiles(array $files): Collection
    {
        return (new Collection($files))
            ->map(function (string $item) {
                return $this->resolvePath($item);
            })
            ->partition(function ($item) {
                return $this->fileSystem->isDir($item);
            });
    }

    /**
     * @param  string $item
     *
     * @return array|string|string[]
     */
    private function resolvePath(string $item)
    {
        $isAbsolute = Str::startsWith($item, '/');
        $isRelative = Str::startsWith($item, '.');

        $rootDir = Pdk::get('rootDir');

        if (! $isAbsolute && ! $isRelative) {
            $item = sprintf('%s/%s', $rootDir, $item);
        } elseif ($isRelative) {
            $item = sprintf('%s/%s', $rootDir, Str::after($item, './'));
        }

        return $this->fileSystem->realpath(str_replace('//', '/', $item));
    }
}

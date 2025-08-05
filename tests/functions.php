<?php
/** @noinspection PhpDocMissingThrowsInspection */

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\FactoryFactory;
use ZipArchive;

const TMP_DIR = __DIR__ . '/../.tmp';

function mockPdkProperties(array $properties): callable
{
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $mockPdk */
    $mockPdk = Pdk::get(PdkInterface::class);

    $oldValues = [];

    foreach ($properties as $property => $value) {
        $oldValues[$property] = $mockPdk->get($property);

        $mockPdk->set($property, $value);
    }

    return static function () use ($mockPdk, $oldValues) {
        foreach ($oldValues as $property => $value) {
            $mockPdk->set($property, $value);
        }
    };
}

function mockPdkProperty(string $property, $value): callable
{
    return mockPdkProperties([$property => $value]);
}

/**
 * @param  class-string<\MyParcelNL\Pdk\Base\Model\Model|\MyParcelNL\Pdk\Base\Support\Collection> $class
 * @param  mixed                                                                                  ...$args
 */
function factory(string $class, ...$args)
{
    return FactoryFactory::create($class, ...$args);
}

/**
 * Read the contents of a zip file into an array.
 *
 * @param  string $filename
 *
 * @return array
 */
function readZip(string $filename): array
{
    $zip = new ZipArchive();
    $zip->open($filename);

    $files = [];

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat     = $zip->statIndex($i);
        $contents = $zip->getFromIndex($i);

        $files[$stat['name']] = $contents;
    }

    $zip->close();

    return $files;
}

/**
 * @param  string $dir
 * @param  bool   $deleteDir
 *
 * @return void
 */
function deleteTemporaryFiles(string $dir = TMP_DIR, bool $deleteDir = false): void
{
    $paths = scandir($dir);

    foreach ($paths as $path) {
        if ('.' === $path || '..' === $path) {
            continue;
        }

        if (is_dir("$dir/$path")) {
            deleteTemporaryFiles("$dir/$path", true);
        } else {
            unlink("$dir/$path");
        }
    }

    if ($deleteDir) {
        rmdir($dir);
    }
}

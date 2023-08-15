<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Base;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\FileSystem;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        FileSystemInterface::class => autowire(FileSystem::class),
    ])
);

it('creates directories', function () {
    /** @var FileSystemInterface $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);
    $fileSystem->mkdir(__DIR__ . '/.test');

    expect(is_dir(__DIR__ . '/.test'))->toBeTrue();

    rmdir(__DIR__ . '/.test');
});

it('deletes files', function () {
    /** @var FileSystemInterface $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);
    $fileSystem->put(__DIR__ . '/.test-file-to-delete', 'test');

    expect($fileSystem->fileExists(__DIR__ . '/.test-file-to-delete'))->toBeTrue();

    $fileSystem->unlink(__DIR__ . '/.test-file-to-delete');

    expect($fileSystem->fileExists(__DIR__ . '/.test-file-to-delete'))->toBeFalse();
});

it('checks if files exist', function () {
    /** @var FileSystemInterface $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);
    $fileSystem->put(__DIR__ . '/.test-file-exists', 'test');

    expect($fileSystem->fileExists(__DIR__ . '/.test-file-exists'))->toBeTrue();

    $fileSystem->unlink(__DIR__ . '/.test-file-exists');

    expect($fileSystem->fileExists(__DIR__ . '/.test-file-exists'))->toBeFalse();
});

it('puts and gets files', function () {
    /** @var FileSystemInterface $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);
    $fileSystem->put(__DIR__ . '/.test-new-file', 'test');

    expect($fileSystem->get(__DIR__ . '/.test-new-file'))->toBe('test');

    $fileSystem->unlink(__DIR__ . '/.test-new-file');
});

it('throws error when getting nonexistent file', function () {
    /** @var FileSystemInterface $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);

    expect(function () use ($fileSystem) {
        $fileSystem->get(__DIR__ . '/.file-that-does-not-exist');
    })->toThrow(InvalidArgumentException::class);
});

it('checks if something is a directory or a file', function () {
    /** @var FileSystemInterface $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);
    $fileSystem->mkdir(__DIR__ . '/.test-directory');
    $fileSystem->put(__DIR__ . '/.test-file', 'test');

    expect($fileSystem->isDir(__DIR__ . '/.test-directory'))
        ->toBeTrue()
        ->and($fileSystem->isFile(__DIR__ . '/.test-file'))
        ->toBeTrue()
        ->and($fileSystem->isDir(__DIR__ . '/.test-file'))
        ->toBeFalse()
        ->and($fileSystem->isFile(__DIR__ . '/.test-directory'))
        ->toBeFalse();

    rmdir(__DIR__ . '/.test-directory');
    $fileSystem->unlink(__DIR__ . '/.test-file');
});


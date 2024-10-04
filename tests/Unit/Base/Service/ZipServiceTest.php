<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Pdk\Base\Contract\ZipServiceInterface;
use MyParcelNL\Pdk\Base\Exception\ZipException;
use MyParcelNL\Pdk\Base\FileSystem;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\readZip;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('creates a zip file', function () {
    $appInfo = Pdk::getAppInfo();
    /** @var \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface $zipService */
    $zipService = Pdk::get(ZipServiceInterface::class);
    /** @var FileSystem $realFileSystem */
    $realFileSystem = Pdk::get(FileSystem::class);

    $filename = $appInfo->createPath('test.zip');

    $zipService->create($filename);
    $zipService->addFromString('test', 'test.txt');
    $zipService->close();

    expect($realFileSystem->fileExists($filename))->toBeTrue();

    $contents = readZip($filename);

    expect($contents)->toEqual([
        'test.txt' => 'test',
    ]);

    // Clean up created files
    $realFileSystem->unlink($filename);
});

it('adds files to a zip', function () {
    $appInfo = Pdk::getAppInfo();
    /** @var \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface $zipService */
    $zipService = Pdk::get(ZipServiceInterface::class);
    /** @var FileSystem $realFileSystem */
    $realFileSystem = Pdk::get(FileSystem::class);

    $filename = $appInfo->createPath('test-from-files.zip');

    $realFileSystem->put($appInfo->createPath('some-file.txt'), 'test some file');
    $realFileSystem->put($appInfo->createPath('some-other-file.txt'), 'test some other file');

    $zipService->create($filename);

    $zipService->addFile($appInfo->createPath('some-file.txt'));
    $zipService->addFile($appInfo->createPath('some-other-file.txt'), 'nested/some-renamed-file.txt');

    $zipService->close();

    expect($realFileSystem->fileExists($filename))->toBeTrue();

    $contents = readZip($filename);

    expect($contents)->toEqual([
        'some-file.txt'                => 'test some file',
        'nested/some-renamed-file.txt' => 'test some other file',
    ]);

    // Clean up created files
    $realFileSystem->unlink($filename);
    $realFileSystem->unlink($appInfo->createPath('some-file.txt'));
    $realFileSystem->unlink($appInfo->createPath('some-other-file.txt'));
});

it('throws error when calling method while no zip is open', function (callable $callback) {
    /** @var \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface $zipService */
    $zipService = Pdk::get(ZipServiceInterface::class);

    $callback($zipService);
})
    ->throws(ZipException::class)
    ->with([
        'addFromString' => function () {
            return function (ZipServiceInterface $zipService) {
                $zipService->addFromString('test', 'test.txt');
            };
        },

        'addFile' => function () {
            return function (ZipServiceInterface $zipService) {
                $zipService->addFile('some-file.txt');
            };
        },

        'close' => function () {
            return function (ZipServiceInterface $zipService) {
                $zipService->close();
            };
        },
    ]);

<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Pdk\Base\Contract\ZipServiceInterface;
use MyParcelNL\Pdk\Base\Exception\ZipException;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesRealFileSystem;
use function MyParcelNL\Pdk\Tests\readZip;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesRealFileSystem());

it('creates a zip file', function () {
    /** @var \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface $zipService */
    $zipService = Pdk::get(ZipServiceInterface::class);
    /** @var \MyParcelNL\Pdk\Base\FileSystem $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);

    $appInfo  = Pdk::getAppInfo();
    $filename = $appInfo->createPath('test.zip');

    $zipService->create($filename);
    $zipService->addFromString('test', 'test.txt');
    $zipService->close();

    expect($fileSystem->fileExists($filename))->toBeTrue();

    $contents = readZip($filename);

    expect($contents)->toEqual([
        'test.txt' => 'test',
    ]);
});

it('adds files to a zip', function () {
    /** @var \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface $zipService */
    $zipService = Pdk::get(ZipServiceInterface::class);
    /** @var \MyParcelNL\Pdk\Base\FileSystem $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);

    $appInfo  = Pdk::getAppInfo();
    $filename = $appInfo->createPath('test-from-files.zip');

    $fileSystem->put($appInfo->createPath('some-file.txt'), 'test some file');
    $fileSystem->put($appInfo->createPath('some-other-file.txt'), 'test some other file');

    $zipService->create($filename);

    $zipService->addFile($appInfo->createPath('some-file.txt'));
    $zipService->addFile($appInfo->createPath('some-other-file.txt'), 'nested/some-renamed-file.txt');

    $zipService->close();

    expect($fileSystem->fileExists($filename))->toBeTrue();

    $contents = readZip($filename);

    expect($contents)->toEqual([
        'some-file.txt'                => 'test some file',
        'nested/some-renamed-file.txt' => 'test some other file',
    ]);
});

it('throws error when calling method while no zip is open', function (callable $callback) {
    /** @var \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface $zipService */
    $zipService = Pdk::get(ZipServiceInterface::class);
    /** @var \MyParcelNL\Pdk\Base\FileSystem $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);

    $callback($zipService, $fileSystem);
})
    ->throws(ZipException::class)
    ->with([
        'addFromString' => function () {
            return function (ZipServiceInterface $zipService) {
                $zipService->addFromString('test', 'test.txt');
            };
        },

        'addFile' => function () {
            return function (ZipServiceInterface $zipService, FileSystemInterface $fileSystem) {
                $appInfo = Pdk::getAppInfo();
                $path    = $appInfo->createPath('some-file.txt');

                $fileSystem->put($path, 'test some file');
                $zipService->addFile($path);
            };
        },

        'close' => function () {
            return function (ZipServiceInterface $zipService) {
                $zipService->close();
            };
        },
    ]);

it('throws error when adding a file fails', function () {
    $appInfo = Pdk::getAppInfo();
    /** @var \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface $zipService */
    $zipService = Pdk::get(ZipServiceInterface::class);

    $zipFilename = $appInfo->createPath('test.zip');

    $zipService->create($zipFilename);

    // Adding file that does not exist
    $zipService->addFile($appInfo->createPath('some-file.txt'));
})->throws(ZipException::class);

it('throws error when fails to open zip file', function () {
    /** @var \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface $zipService */
    $zipService = Pdk::get(ZipServiceInterface::class);
    /** @var \MyParcelNL\Pdk\Base\FileSystem $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);

    $appInfo = Pdk::getAppInfo();
    $path    = $appInfo->createPath('some-file.txt');

    // Creating file in the place of the zip file
    $fileSystem->put($path, 'test some file');

    // Throws exception because file already exists
    $zipService->create($path);
})->throws(ZipException::class);

it('throws error when closing zip file fails', function () {
    /** @var \MyParcelNL\Pdk\Base\Contract\ZipServiceInterface $zipService */
    $zipService = Pdk::get(ZipServiceInterface::class);
    /** @var \MyParcelNL\Pdk\Base\FileSystem $fileSystem */
    $fileSystem = Pdk::get(FileSystemInterface::class);

    $appInfo     = Pdk::getAppInfo();
    $zipFilename = $appInfo->createPath('test.zip');

    $zipService->create($zipFilename);

    $filename = $appInfo->createPath('some-file.txt');
    $fileSystem->put($filename, 'test some file');

    // Add a file
    $zipService->addFile($filename);
    // Then delete that file before closing the zip, triggering exception on close.
    $fileSystem->unlink($filename);

    $zipService->close();
})->throws(ZipException::class);

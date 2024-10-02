<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Debug;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Base\FileSystem;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use function DI\get;
use function MyParcelNL\Pdk\Tests\readZip;
use function MyParcelNL\Pdk\Tests\usesShared;
use const PHP_EOL;

usesShared(new UsesMockPdkInstance([
    // Using real file system because we are not mocking ZipArchive.
    FileSystemInterface::class => get(FileSystem::class),
]));

test('it downloads logs', function () {
    // Warning and notice are not called to check if they're omitted from the created zip for being empty.
    Logger::emergency('emergency message');
    Logger::alert('hi');
    Logger::critical('some string');
    Logger::error('error message');
    Logger::info('info message with context', ['some' => 'context']);
    Logger::debug('debug message');
    Logger::debug('debug message 2');
    Logger::debug('debug message 3');

    $request = new Request(['action' => PdkBackendActions::DOWNLOAD_LOGS]);

    /** @var \Symfony\Component\HttpFoundation\BinaryFileResponse $response */
    $response = Actions::execute($request);

    $file = $response->getFile();

    expect($response)
        ->toBeInstanceOf(BinaryFileResponse::class)
        ->and($response->getStatusCode())
        ->toBe(200)
        ->and($file->isFile())
        ->toBeTrue()
        ->and($file->getFilename())
        ->toMatch('/\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}_pest_logs.zip$/');

    // Check if the returned zip file contains the logs
    $logs = readZip($file->getPathname());

    expect($logs)->toEqual([
        'test_emergency.log' => '!emergency! [PDK]: emergency message' . PHP_EOL,
        'test_alert.log'     => '!alert! [PDK]: hi' . PHP_EOL,
        'test_critical.log'  => '!critical! [PDK]: some string' . PHP_EOL,
        'test_error.log'     => '!error! [PDK]: error message' . PHP_EOL,
        'test_info.log'      => '!info! [PDK]: info message with context {"some":"context"}' . PHP_EOL,
        'test_debug.log'     => implode(PHP_EOL, [
                '!debug! [PDK]: debug message',
                '!debug! [PDK]: debug message 2',
                '!debug! [PDK]: debug message 3',
            ]) . PHP_EOL,
        'test_notice.log'    => '',
        'test_warning.log'   => '',
    ]);

    // Send the response to check if the created file is deleted after sending
    $response->send();

    expect($file->isFile())->toBeFalse();
});

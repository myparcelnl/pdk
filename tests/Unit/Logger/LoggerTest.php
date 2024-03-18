<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Logger;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk as PdkFacade;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesEachMockPdkInstance());

it('logs logs', function (string $level, string $message, array $context = []) {
    Logger::{$level}($message, $context);

    expect(Logger::getLogs())->toBe([
        [
            'level'   => $level,
            'message' => "[PDK]: $message",
            'context' => $context,
        ],
    ]);
})->with([
    'debug'     => [
        'level'   => 'debug',
        'message' => 'Some nice padding for the logs',
        'context' => ['additional' => 'information'],
    ],
    'info'      => [
        'level'   => 'info',
        'message' => 'This is informative',
    ],
    'notice'    => [
        'level'   => 'notice',
        'message' => 'This may be important',
    ],
    'warning'   => [
        'level'   => 'warning',
        'message' => 'Someone should check this out',
    ],
    'error'     => [
        'level'   => 'error',
        'message' => 'This is pretty bad',
    ],
    'critical'  => [
        'level'   => 'critical',
        'message' => 'This is REALLY bad',
    ],
    'alert'     => [
        'level'   => 'alert',
        'message' => 'Calling all code owners',
    ],
    'emergency' => [
        'level'   => 'emergency',
        'message' => 'The world has ended',
    ],
]);

it('logs deprecations', function (string $currentVersion, string $nextVersion) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockFileSystem $fileSystem */
    $fileSystem = PdkFacade::get(FileSystemInterface::class);
    $rootDir    = PdkFacade::get('rootDir');

    $fileSystem->put($rootDir . '/composer.json', json_encode([
        'name'    => 'myparcelnl/pdk',
        'version' => $currentVersion,
    ]));

    Logger::deprecated('old', 'new', ['additional' => 'information']);

    expect(Logger::getLogs())->toBe([
        [
            'level'   => 'notice',
            'message' => "[PDK]: [DEPRECATED] old is deprecated. Use new instead. Will be removed in $nextVersion.",
            'context' => ['additional' => 'information'],
        ],
    ]);
})->with([
    ['1.0.0', '2.0.0'],
    ['2.1.3', '3.0.0'],
    ['5555.33312.1231245392', '5556.0.0'],
]);

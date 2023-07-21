<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Logger;

use MyParcelNL\Pdk\Facade\Logger;
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

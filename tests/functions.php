<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Facade\Pdk;

function mockPlatform(string $platform): callable
{
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $mockPdk */
    $mockPdk     = Pdk::get(PdkInterface::class);
    $oldPlatform = $mockPdk->get('platform');

    $mockPdk->set('platform', $platform);

    return static function () use ($mockPdk, $oldPlatform) {
        $mockPdk->set('platform', $oldPlatform);
    };
}

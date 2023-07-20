<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

use MyParcelNL\Pdk\Facade\Logger;

final class MockDeprecatedClass
{
    public function __construct()
    {
        Logger::reportDeprecatedClass(__CLASS__, 'OtherClass');
    }

    public function deprecatedMethod(): void
    {
        Logger::reportDeprecatedMethod(__METHOD__, 'OtherClass::otherMethod');
    }
}

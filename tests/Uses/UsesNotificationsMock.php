<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Facade\Notifications;

final class UsesNotificationsMock implements BaseMock
{
    public function afterEach(): void
    {
        Notifications::clear();
    }
}

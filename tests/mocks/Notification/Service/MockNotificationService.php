<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Service;

use MyParcelNL\Pdk\Contract\MockServiceInterface;

final class MockNotificationService extends NotificationService implements MockServiceInterface
{
    public function reset(): void
    {
        $this->clear();
    }
}

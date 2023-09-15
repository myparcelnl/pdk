<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Status\Service\AbstractStatusService;

class MockStatusService extends AbstractStatusService
{
    public function changeOrderStatus(array $orderIds, string $status): void
    {
    }
}

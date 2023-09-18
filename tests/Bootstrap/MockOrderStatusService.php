<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;

class MockOrderStatusService implements OrderStatusServiceInterface
{
    public function all(): array
    {
        return [
            'pending'   => 'Pending',
            'paid'      => 'Paid',
            'shipped'   => 'Shipped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded'  => 'Refunded',
        ];
    }

    public function updateStatus(array $orderIds, $status)
    {
    }
}

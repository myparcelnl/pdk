<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Service;

use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;

final class MockOrderStatusService implements OrderStatusServiceInterface
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
}

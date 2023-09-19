<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use Symfony\Contracts\Service\ResetInterface;

final class MockOrderStatusService implements OrderStatusServiceInterface, ResetInterface
{
    /**
     * @var array[]
     */
    private $updates = [];

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

    /**
     * @return array[]
     */
    public function getUpdates(): array
    {
        return $this->updates;
    }

    public function reset(): void
    {
        $this->updates = [];
    }

    /**
     * @param  array       $orderIds
     * @param  null|string $status
     *
     * @return void
     */
    public function updateStatus(array $orderIds, ?string $status)
    {
        $this->updates[] = [
            'orderIds' => $orderIds,
            'status'   => $status,
        ];
    }
}

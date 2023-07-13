<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository;

class MockPdkOrderRepository extends AbstractPdkOrderRepository
{
    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder ...$orders
     *
     * @return void
     */
    public function add(PdkOrder ...$orders): void
    {
        foreach ($orders as $order) {
            $this->save($order->externalIdentifier, $order);
        }
    }

    /**
     * @param  int|string $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function get($input): PdkOrder
    {
        $orderData = is_array($input) ? $input : ['externalIdentifier' => $input];

        return $this->retrieve((string) $orderData['externalIdentifier'], function () use ($orderData) {
            return new PdkOrder($orderData);
        });
    }

    /**
     * @param  null|string $externalIdentifier
     *
     * @return array
     */
    public function getOrderNotes(?string $externalIdentifier): array
    {
        return [];
    }
}

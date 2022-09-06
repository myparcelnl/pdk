<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Repository\ApiPdkOrderRepository;

class MockPdkOrderRepository extends ApiPdkOrderRepository
{
    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder ...$orders
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
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     */
    public function get($input): PdkOrder
    {
        $orderData = is_array($input) ? $input : ['externalIdentifier' => $input];

        return $this->retrieve((string) $orderData['externalIdentifier'], function () use ($orderData) {
            return new PdkOrder($orderData);
        });
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

final class MockPdkOrderRepository extends AbstractPdkOrderRepository
{
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
}

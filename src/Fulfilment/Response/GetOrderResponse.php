<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Fulfilment\Model\Order;

final class GetOrderResponse extends GetOrdersResponse
{
    public function getOrder(): Order
    {
        return $this
            ->getOrders()
            ->first();
    }
}

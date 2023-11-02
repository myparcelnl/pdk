<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

final class GetOrderResponse extends GetOrdersResponse
{
    /**
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function getOrder(): PdkOrder
    {
        return $this
            ->getOrders()
            ->first();
    }
}

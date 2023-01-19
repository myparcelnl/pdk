<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Request\Orders;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

class FetchOrdersEndpointRequest extends AbstractEndpointRequest
{
    /**
     * @return string
     */
    public function getProperty(): string
    {
        return 'orders';
    }
}

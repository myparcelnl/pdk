<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Orders;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

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

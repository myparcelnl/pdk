<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Orders;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

class UpdateOrdersEndpointRequest extends AbstractEndpointRequest
{
    public function getMethod(): string
    {
        return 'POST';
    }

    public function getProperty(): string
    {
        return 'orders';
    }
}

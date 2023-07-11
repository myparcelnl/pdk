<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Orders;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

final class SynchronizeOrdersEndpointRequest extends AbstractEndpointRequest
{
    public function getMethod(): string
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return 'orders';
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Request\Orders;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

class ExportOrdersEndpointRequest extends AbstractEndpointRequest
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

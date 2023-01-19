<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Request\Shipment;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

class ExportReturnEndpointRequest extends AbstractEndpointRequest
{
    /**
     * @return string
     */
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

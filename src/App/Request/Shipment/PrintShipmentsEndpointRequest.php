<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Shipment;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

class PrintShipmentsEndpointRequest extends AbstractEndpointRequest
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
        return 'pdfs';
    }
}

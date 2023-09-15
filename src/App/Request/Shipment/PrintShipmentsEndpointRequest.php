<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Shipment;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

class PrintShipmentsEndpointRequest extends AbstractEndpointRequest
{
    public function getMethod(): string
    {
        return 'POST';
    }

    public function getProperty(): string
    {
        return 'pdfs';
    }
}

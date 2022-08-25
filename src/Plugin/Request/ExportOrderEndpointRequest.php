<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Request;

class ExportOrderEndpointRequest extends AbstractEndpointRequest
{
    public function getMethod(): string
    {
        return 'POST';
    }
}

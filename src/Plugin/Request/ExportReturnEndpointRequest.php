<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Request;

class ExportReturnEndpointRequest extends AbstractEndpointRequest
{
    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'POST';
    }
}

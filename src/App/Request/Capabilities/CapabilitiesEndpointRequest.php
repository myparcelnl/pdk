<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Capabilities;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

class CapabilitiesEndpointRequest extends AbstractEndpointRequest
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
        return 'capabilities';
    }
}

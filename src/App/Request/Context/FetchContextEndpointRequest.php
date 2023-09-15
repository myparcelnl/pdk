<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Context;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

class FetchContextEndpointRequest extends AbstractEndpointRequest
{
    public function getProperty(): string
    {
        return 'context';
    }
}

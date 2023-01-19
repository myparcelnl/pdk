<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Request\Context;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

class FetchContextEndpointRequest extends AbstractEndpointRequest
{
    /**
     * @return string
     */
    public function getProperty(): string
    {
        return 'context';
    }
}

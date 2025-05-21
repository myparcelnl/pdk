<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Addresses;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

class AddressesValidateEndpointRequest extends AbstractEndpointRequest
{
    /**
     * @return string
     */
    public function getProperty(): string
    {
        return 'validate';
    }
} 

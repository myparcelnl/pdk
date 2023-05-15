<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Settings;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

class UpdateProductSettingsEndpointRequest extends AbstractEndpointRequest
{
    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'PUT';
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return 'product_settings';
    }
}

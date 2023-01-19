<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Request\Settings;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

class UpdatePluginSettingsEndpointRequest extends AbstractEndpointRequest
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
        return 'plugin_settings';
    }
}

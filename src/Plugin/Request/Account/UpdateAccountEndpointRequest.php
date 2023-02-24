<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Request\Account;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

class UpdateAccountEndpointRequest extends AbstractEndpointRequest
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
        return 'account_settings';
    }

    /**
     * @return string
     */
    public function getResponseProperty(): string
    {
        return 'context';
    }
}

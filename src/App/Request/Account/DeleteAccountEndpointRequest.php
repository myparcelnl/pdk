<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Account;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

final class DeleteAccountEndpointRequest extends AbstractEndpointRequest
{
    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'POST';
    }
}

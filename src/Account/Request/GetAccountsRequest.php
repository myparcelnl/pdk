<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

class GetAccountsRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $path = '/accounts';

    public function getHttpMethod(): string
    {
        return 'GET';
    }
}

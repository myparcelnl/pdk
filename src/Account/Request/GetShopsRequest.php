<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

use MyParcelNL\Pdk\Base\Request\AbstractRequest;

class GetShopsRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $path = '/shops';

    public function getHttpMethod(): string
    {
        return 'GET';
    }
}

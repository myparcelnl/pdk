<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetOrdersRequest extends Request
{
    /**
     * @return string
     */
    public function getPath(): string
    {
        return '/fulfilment/orders';
    }
}

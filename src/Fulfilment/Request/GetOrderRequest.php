<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Api\Request\Request;

final class GetOrderRequest extends Request
{
    public function __construct(private readonly string $uuid)
    {
        parent::__construct();
    }

    public function getPath(): string
    {
        return sprintf('/fulfilment/orders/%s', $this->uuid);
    }
}

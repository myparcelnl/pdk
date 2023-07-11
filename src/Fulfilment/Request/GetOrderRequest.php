<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Api\Request\Request;

final class GetOrderRequest extends Request
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @param  string $uuid
     */
    public function __construct(string $uuid)
    {
        parent::__construct();
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return sprintf('/fulfilment/orders/%s', $this->uuid);
    }
}

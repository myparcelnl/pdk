<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetShopRequest extends Request
{
    public function __construct(private readonly int $shopId)
    {
        parent::__construct();
    }

    public function getPath(): string
    {
        return '/shops';
    }

    protected function getParameters(): array
    {
        return [
            'id' => $this->shopId,
        ];
    }
}

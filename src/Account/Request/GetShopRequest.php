<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Request;

use MyParcelNL\Pdk\Api\Request\Request;

class GetShopRequest extends Request
{
    /**
     * @var int
     */
    private $shopId;

    /**
     * @param  int $shopId
     */
    public function __construct(int $shopId)
    {
        parent::__construct();
        $this->shopId = $shopId;
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

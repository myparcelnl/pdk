<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;

class GetShopsResponse extends ApiResponseWithBody
{
    private ?Shop $shop = null;

    public function getShop(): Shop
    {
        return $this->shop;
    }

    protected function parseResponseBody(): void
    {
        $data       = json_decode($this->getBody(), true)['data']['shops'][0];
        $this->shop = new Shop($data);
    }
}

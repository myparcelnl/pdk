<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponse;
use MyParcelNL\Sdk\src\Model\Account\Shop;

class GetShopsResponse extends AbstractApiResponse
{
    /**
     * @var mixed
     */
    private $shop;

    /**
     * @return \MyParcelNL\Sdk\src\Model\Account\Shop;
     */
    public function getShop(): Shop
    {
        return $this->shop;
    }

    protected function parseResponseBody(string $body): void
    {
        $data       = json_decode($body, true)['data']['shops'][0];
        $this->shop = new Shop($data);
    }
}

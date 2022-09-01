<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Sdk\src\Model\Account\Shop;

class GetShopsResponseWithBody extends AbstractApiResponseWithBody
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

    protected function parseResponseBody(): void
    {
        $data       = json_decode($this->getBody(), true)['data']['shops'][0];
        $this->shop = new Shop($data);
    }
}

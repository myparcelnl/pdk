<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetShopsRequest;
use MyParcelNL\Pdk\Account\Response\GetShopsResponse;
use MyParcelNL\Pdk\Repository\AbstractRepository;
use MyParcelNL\Sdk\src\Model\Account\Shop;

class ShopRepository extends AbstractRepository
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getShop(): Shop
    {
        return $this->retrieve('shop', function () {
            /** @var \MyParcelNL\Pdk\Account\Response\GetShopsResponse $response */
            $response = $this->api->doRequest(new GetShopsRequest(), GetShopsResponse::class);

            return $response->getShop();
        });
    }
}

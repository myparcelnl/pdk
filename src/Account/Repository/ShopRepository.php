<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Account\Request\GetShopsRequest;
use MyParcelNL\Pdk\Account\Response\GetShopsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;

class ShopRepository extends ApiRepository
{
    /**
     * @return \MyParcelNL\Pdk\Account\Model\Shop
     * @noinspection PhpUnused
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

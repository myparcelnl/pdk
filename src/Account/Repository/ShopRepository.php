<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Request\GetShopsRequest;
use MyParcelNL\Pdk\Account\Response\GetShopsResponseWithBody;
use MyParcelNL\Pdk\Base\Repository\AbstractRepository;
use MyParcelNL\Sdk\src\Model\Account\Shop;

class ShopRepository extends AbstractRepository
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     */
    public function getShop(): Shop
    {
        return $this->retrieve('shop', function () {
            /** @var \MyParcelNL\Pdk\Account\Response\GetShopsResponseWithBody $response */
            $response = $this->api->doRequest(new GetShopsRequest(), GetShopsResponseWithBody::class);

            return $response->getShop();
        });
    }
}

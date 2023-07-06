<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;

class PostOrdersResponse extends ApiResponseWithBody
{
    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    private $orderCollection;

    /**
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    public function getOrderCollection(): OrderCollection
    {
        return $this->orderCollection;
    }

    protected function parseResponseBody(): void
    {
        $parsedBody      = json_decode($this->getBody(), true);
        $orderCollection = new OrderCollection();

        foreach ($parsedBody['data']['orders'] as $order) {
            $orderCollection->push($order);
        }

        $this->orderCollection = $orderCollection;
    }
}

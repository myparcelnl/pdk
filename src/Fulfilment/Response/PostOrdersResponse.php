<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
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
        $parsedBody            = json_decode($this->getBody(), true);
        $this->orderCollection = new OrderCollection($parsedBody['data']['orders']);
    }
}

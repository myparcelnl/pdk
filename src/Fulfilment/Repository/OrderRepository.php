<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\Base\Repository\AbstractRepository;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Request\GetOrdersRequest;
use MyParcelNL\Pdk\Fulfilment\Request\PostOrdersRequest;
use MyParcelNL\Pdk\Fulfilment\Response\GetOrdersResponse;
use MyParcelNL\Pdk\Fulfilment\Response\PostOrdersResponse;

class OrderRepository extends AbstractRepository
{
    /**
     * @param  array $parameters
     *
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     * @noinspection PhpUnused
     */
    public function query(array $parameters): OrderCollection
    {
        $request = new GetOrdersRequest($parameters);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Fulfilment\Response\GetOrdersResponse $response */
            $response = $this->api->doRequest($request, GetOrdersResponse::class);

            return $response->getOrders();
        });
    }

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection $collection
     *
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     * @noinspection PhpUnused
     */
    public function saveOrder(OrderCollection $collection): OrderCollection
    {
        /** @var \MyParcelNL\Pdk\Fulfilment\Response\PostOrdersResponse $response */
        $response = $this->api->doRequest(new PostOrdersRequest($collection), PostOrdersResponse::class);

        return $collection->addIds($response->getIds());
    }
}

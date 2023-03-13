<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\Api\Response\PostIdsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Request\GetOrdersRequest;
use MyParcelNL\Pdk\Fulfilment\Request\PostOrdersRequest;
use MyParcelNL\Pdk\Fulfilment\Response\GetOrdersResponse;

class OrderRepository extends ApiRepository
{
    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection $collection
     *
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     * @noinspection PhpUnused
     */
    public function postOrders(OrderCollection $collection): OrderCollection
    {
        /** @var \MyParcelNL\Pdk\Api\Response\PostIdsResponse $response */
        $response = $this->api->doRequest(new PostOrdersRequest($collection), PostIdsResponse::class);

        return $collection->addIds($response->getIds());
    }

    /**
     * @param  array $parameters
     *
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     * @noinspection PhpUnused
     */
    public function query(array $parameters): OrderCollection
    {
        $request = new GetOrdersRequest(['parameters' => $parameters]);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Fulfilment\Response\GetOrdersResponse $response */
            $response = $this->api->doRequest($request, GetOrdersResponse::class);

            return $response->getOrders();
        });
    }
}

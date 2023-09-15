<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Request\GetOrderRequest;
use MyParcelNL\Pdk\Fulfilment\Request\GetOrdersRequest;
use MyParcelNL\Pdk\Fulfilment\Request\PostOrdersRequest;
use MyParcelNL\Pdk\Fulfilment\Response\GetOrderResponse;
use MyParcelNL\Pdk\Fulfilment\Response\GetOrdersResponse;
use MyParcelNL\Pdk\Fulfilment\Response\PostOrdersResponse;

class OrderRepository extends ApiRepository
{
    public function get(string $uuid): Order
    {
        $request = new GetOrderRequest($uuid);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Fulfilment\Response\GetOrderResponse $response */
            $response = $this->api->doRequest($request, GetOrderResponse::class);

            return $response->getOrder();
        });
    }

    /**
     * @noinspection PhpUnused
     */
    public function postOrders(OrderCollection $collection): OrderCollection
    {
        /** @var \MyParcelNL\Pdk\Fulfilment\Response\PostOrdersResponse $response */
        $response = $this->api->doRequest(new PostOrdersRequest($collection), PostOrdersResponse::class);

        return $response->getOrderCollection();
    }

    /**
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

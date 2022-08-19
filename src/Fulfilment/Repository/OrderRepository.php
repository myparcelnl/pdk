<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\Base\Repository\AbstractRepository;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Request\GetOrdersRequest;
use MyParcelNL\Pdk\Fulfilment\Request\PostOrdersRequest;
use MyParcelNL\Pdk\Fulfilment\Response\OrdersResponse;
use MyParcelNL\Pdk\Shipment\Repository\UpdateOrdersRequest;

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
            /** @var \MyParcelNL\Pdk\Fulfilment\Response\OrdersResponse $response */
            $response = $this->api->doRequest($request, OrdersResponse::class);

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
        /** @var \MyParcelNL\Pdk\Fulfilment\Response\OrdersResponse $response */
        $response = $this->api->doRequest(new PostOrdersRequest($collection), OrdersResponse::class);

        return $response->getOrders();
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     * @param  null|int                                               $size
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     * @noinspection PhpUnused
     */
    public function update(OrderCollection $collection, ?int $size = null): OrderCollection
    {
        $request = new UpdateOrdersRequest($collection, $size);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Fulfilment\Response\OrdersResponse $response */
            $response = $this->api->doRequest($request, OrdersResponse::class);

            return $response->getOrders();
        });
    }
}

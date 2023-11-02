<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Fulfilment\Request\GetOrderRequest;
use MyParcelNL\Pdk\Fulfilment\Request\GetOrdersRequest;
use MyParcelNL\Pdk\Fulfilment\Request\PostOrdersRequest;
use MyParcelNL\Pdk\Fulfilment\Response\GetOrderResponse;
use MyParcelNL\Pdk\Fulfilment\Response\GetOrdersResponse;

class OrderRepository extends ApiRepository
{
    /**
     * @param  string $uuid
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function get(string $uuid): PdkOrder
    {
        $request = new GetOrderRequest($uuid);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Fulfilment\Response\GetOrderResponse $response */
            $response = $this->api->doRequest($request, GetOrderResponse::class);

            return $response->getOrder();
        });
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $collection
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    public function postOrders(PdkOrderCollection $collection): PdkOrderCollection
    {
        /** @var \MyParcelNL\Pdk\Fulfilment\Response\GetOrdersResponse $response */
        $response = $this->api->doRequest(new PostOrdersRequest($collection), GetOrdersResponse::class);

        return $response->getOrders();
    }

    /**
     * @param  array $parameters
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    public function query(array $parameters): PdkOrderCollection
    {
        $request = new GetOrdersRequest(['parameters' => $parameters]);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Fulfilment\Response\GetOrdersResponse $response */
            $response = $this->api->doRequest($request, GetOrdersResponse::class);

            return $response->getOrders();
        });
    }
}

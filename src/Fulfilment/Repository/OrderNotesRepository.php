<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\Api\Response\PostIdsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;
use MyParcelNL\Pdk\Fulfilment\Request\GetOrderNotesRequest;
use MyParcelNL\Pdk\Fulfilment\Request\PostOrderNotesRequest;
use MyParcelNL\Pdk\Fulfilment\Response\GetOrderNotesResponse;

class OrderNotesRepository extends ApiRepository
{
    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection $collection
     * @param  string                                                    $orderId
     *
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection
     * @noinspection PhpUnused
     */
    public function postOrderNotes(OrderNoteCollection $collection, string $orderId): OrderNoteCollection
    {
        /** @var \MyParcelNL\Pdk\Api\Response\PostIdsResponse $response */
        $response = $this->api->doRequest(new PostOrderNotesRequest($collection, $orderId), PostIdsResponse::class);

        return $collection;
    }

    /**
     * @param  array $parameters
     *
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     * @noinspection PhpUnused
     */
    public function query(array $parameters): OrderCollection
    {
        $request = new GetOrderNotesRequest(['parameters' => $parameters]);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Fulfilment\Response\GetOrderNotesResponse $response */
            $response = $this->api->doRequest($request, GetOrderNotesResponse::class);

            return $response->getOrderNotes();
        });
    }
}

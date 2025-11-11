<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;
use MyParcelNL\Pdk\Fulfilment\Request\PostOrderNotesRequest;
use MyParcelNL\Pdk\Fulfilment\Response\PostOrderNotesResponse;

class OrderNotesRepository extends ApiRepository
{
    /**
     * @param  string                                                    $uuid
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection $orderNotes
     *
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection
     * @noinspection PhpUnused
     */
    public function postOrderNotes(string $uuid, OrderNoteCollection $orderNotes): OrderNoteCollection
    {
        if ($orderNotes->isEmpty()) {
            return $orderNotes;
        }

        /** @var PostOrderNotesResponse $response */
        $response = $this->api->doRequest(
            new PostOrderNotesRequest($uuid, $orderNotes),
            PostOrderNotesResponse::class
        );

        return $response->getOrderNotes();
    }
}

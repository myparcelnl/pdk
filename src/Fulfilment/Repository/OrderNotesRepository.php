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
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection $orderNotes
     * @param  null|string                                               $fulfilmentIdentifier
     *
     * @return null|\MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection
     * @noinspection PhpUnused
     */
    public function postOrderNotes(OrderNoteCollection $orderNotes, ?string $fulfilmentIdentifier): ?OrderNoteCollection
    {
        // TODO: Check if shop subscription allows using order notes
        if (! $fulfilmentIdentifier) {
            return null;
        }

        /** @var PostOrderNotesResponse $response */
        $response = $this->api->doRequest(
            new PostOrderNotesRequest($orderNotes, $fulfilmentIdentifier),
            PostOrderNotesResponse::class
        );

        return $response->getOrderNotes();
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Fulfilment\Request\PostOrderNotesRequest;
use MyParcelNL\Pdk\Fulfilment\Response\PostOrderNotesResponse;

class OrderNotesRepository extends ApiRepository
{
    /**
     * @param  string                                                      $uuid
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection $orderNotes
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection
     */
    public function postOrderNotes(string $uuid, PdkOrderNoteCollection $orderNotes): PdkOrderNoteCollection
    {
        /** @var PostOrderNotesResponse $response */
        $response = $this->api->doRequest(
            new PostOrderNotesRequest($uuid, $orderNotes),
            PostOrderNotesResponse::class
        );

        return $response->getOrderNotes();
    }
}

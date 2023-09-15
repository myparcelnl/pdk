<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;

class PostOrderNotesResponse extends ApiResponseWithBody
{
    private ?OrderNoteCollection $orderNotes = null;

    public function getOrderNotes(): OrderNoteCollection
    {
        return $this->orderNotes;
    }

    /**
     * @throws \Exception
     */
    protected function parseResponseBody(): void
    {
        $parsedBody = json_decode($this->getBody(), true);
        $orderNotes = $parsedBody['data']['order_notes'] ?? [];

        $this->createOrderNotes($orderNotes);
    }

    /**
     * @throws \Exception
     */
    private function createOrderNotes(array $orderNotes): void
    {
        $this->orderNotes = new OrderNoteCollection(
            array_map(static fn(array $orderNote) => [
                'uuid'      => $orderNote['uuid'],
                'author'    => $orderNote['author'],
                'note'      => $orderNote['note'],
                'createdAt' => $orderNote['created'],
                'updatedAt' => $orderNote['updated'],
            ], $orderNotes)
        );
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;

class GetOrderNotesResponse extends ApiResponseWithBody
{
    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection
     */
    private $orderNotes;

    /**
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection
     */
    public function getOrderNotes(): OrderNoteCollection
    {
        return $this->orderNotes;
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function parseResponseBody(): void
    {
        $parsedBody = json_decode($this->getBody(), true);
        $orderNotes = $parsedBody['data']['order_notes'] ?? [];

        $this->createOrderNotes($orderNotes);
    }

    /**
     * @param  array $orderNotes
     *
     * @return void
     * @throws \Exception
     */
    private function createOrderNotes(array $orderNotes): void
    {
        $this->orderNotes = (new OrderNoteCollection(
            array_map(static function (array $orderNote) {
                return [
                    'note'   => $orderNote['note'],
                    'author' => $orderNote['author'],
                ];
            }, $orderNotes)
        ));
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;

class PostOrderNotesResponse extends ApiResponseWithBody
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection
     */
    private $orderNotes;

    /**
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection
     */
    public function getOrderNotes(): PdkOrderNoteCollection
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
        $this->orderNotes = new PdkOrderNoteCollection(
            array_map(static function (array $orderNote) {
                return [
                    'apiIdentifier' => $orderNote['uuid'],
                    'author'        => $orderNote['author'],
                    'note'          => $orderNote['note'],
                    'createdAt'     => $orderNote['created'],
                    'updatedAt'     => $orderNote['updated'],
                ];
            }, $orderNotes)
        );
    }
}

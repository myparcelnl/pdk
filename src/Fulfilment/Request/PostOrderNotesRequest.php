<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;

class PostOrderNotesRequest extends Request
{
    public function __construct(private readonly string $orderId, private readonly OrderNoteCollection $collection)
    {
        parent::__construct();
    }

    /**
     * @return null|string
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'order_notes' => array_map(static fn(OrderNote $orderNote) => [
                    'author' => $orderNote->author,
                    'note'   => $orderNote->note,
                ], $this->collection->all()),
            ],
        ]);
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getPath(): string
    {
        return sprintf('/fulfilment/orders/%s/notes', $this->orderId);
    }
}

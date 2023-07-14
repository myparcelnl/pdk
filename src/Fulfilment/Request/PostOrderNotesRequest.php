<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;

class PostOrderNotesRequest extends Request
{
    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    private $collection;

    /**
     * @var string
     */
    private $orderId;

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection $collection
     * @param  string                                                    $orderId
     */
    public function __construct(OrderNoteCollection $collection, string $orderId)
    {
        parent::__construct();
        $this->collection = $collection;
        $this->orderId    = $orderId;
    }

    /**
     * @return null|string
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'order_notes' => array_map(function (OrderNote $orderNote) {
                    return [
                        'note'   => $orderNote->note,
                        'author' => $orderNote->author,
                    ];
                }, $this->collection->all()),
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return sprintf('/fulfilment/orders/%s/notes', $this->orderId);
    }
}

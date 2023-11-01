<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;

class PostOrderNotesRequest extends Request
{
    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection
     */
    private $collection;

    /**
     * @var string
     */
    private $orderId;

    /**
     * @param  string                                                    $orderId
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection $collection
     */
    public function __construct(string $orderId, OrderNoteCollection $collection)
    {
        parent::__construct();
        $this->collection = $collection;
        $this->orderId    = $orderId;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'order_notes' => array_map(static function (OrderNote $orderNote) {
                    return [
                        'author' => $orderNote->author,
                        'note'   => $orderNote->note,
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

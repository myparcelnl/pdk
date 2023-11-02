<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;

class PostOrderNotesRequest extends Request
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection
     */
    private $collection;

    /**
     * @var string
     */
    private $orderId;

    /**
     * @param  string                                                      $orderId
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection $collection
     */
    public function __construct(string $orderId, PdkOrderNoteCollection $collection)
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
                'order_notes' => array_map(static function (PdkOrderNote $orderNote) {
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

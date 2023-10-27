<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;

/**
 * @property null|string    $externalIdentifier
 * @property null|string    $orderIdentifier
 * @property null|string    $apiIdentifier
 * @property null|string    $author
 * @property null|string    $note
 * @property null|\DateTime $createdAt
 * @property null|\DateTime $updatedAt
 */
class PdkOrderNote extends Model
{
    public    $attributes = [
        /**
         * The id of the order this note belongs to in the external system.
         */
        'externalIdentifier' => null,

        /**
         * The id of the order this note belongs to.
         */
        'orderIdentifier'    => null,

        /**
         * The id of the note in the fulfilment api.
         */
        'apiIdentifier'      => null,

        'author'    => null,
        'note'      => null,
        'createdAt' => null,
        'updatedAt' => null,
    ];

    protected $casts      = [
        'externalIdentifier' => 'string',
        'orderIdentifier'    => 'string',
        'apiIdentifier'      => 'string',
        'author'             => 'string',
        'note'               => 'string',
        'createdAt'          => 'datetime',
        'updatedAt'          => 'datetime',
    ];

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Model\OrderNote $orderNote
     *
     * @return self
     * @noinspection PhpUnused
     */
    public static function fromFulfilmentOrderNote(OrderNote $orderNote): self
    {
        return new self([
            'apiIdentifier' => $orderNote->uuid,
            'author'        => $orderNote->author,
            'note'          => $orderNote->note,
            'createdAt'     => $orderNote->createdAt,
            'updatedAt'     => $orderNote->updatedAt,
        ]);
    }
}

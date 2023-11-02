<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Base\Model\Model;

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
    public const AUTHOR_CUSTOMER = 'customer';
    public const AUTHOR_WEBSHOP  = 'webshop';

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
}

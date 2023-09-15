<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string    $uuid
 * @property null|string    $author - 'customer' or 'webshop'
 * @property null|string    $note
 * @property null|\DateTime $createdAt
 * @property null|\DateTime $updatedAt
 */
class OrderNote extends Model
{
    final public const AUTHOR_CUSTOMER = 'customer';
    final public const AUTHOR_WEBSHOP  = 'webshop';

    protected $attributes = [
        'uuid'      => null,
        'author'    => null,
        'note'      => null,
        'createdAt' => null,
        'updatedAt' => null,
    ];

    protected $casts      = [
        'uuid'      => 'string',
        'author'    => 'string',
        'note'      => 'string',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public static function fromPdkOrderNote(PdkOrderNote $pdkOrderNote): self
    {
        return new self([
            'uuid'      => $pdkOrderNote->apiIdentifier,
            'author'    => $pdkOrderNote->author,
            'note'      => $pdkOrderNote->note,
            'createdAt' => $pdkOrderNote->createdAt,
            'updatedAt' => $pdkOrderNote->updatedAt,
        ]);
    }
}

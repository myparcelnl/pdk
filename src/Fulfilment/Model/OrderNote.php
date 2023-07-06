<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string $note
 * @property null|string $author
 */
class OrderNote extends Model
{
    protected $attributes = [
        'note'   => null,
        'author' => 1,
    ];

    protected $casts      = [
        'note'   => 'string',
        'author' => 'string',
    ];
}

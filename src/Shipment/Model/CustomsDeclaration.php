<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property int                                                             $contents
 * @property \MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItemCollection $items
 * @property null|string                                                     $invoice
 * @property int                                                             $weight
 */
class CustomsDeclaration extends Model
{
    public const CONTENTS_COMMERCIAL_GOODS   = 1;
    public const CONTENTS_COMMERCIAL_SAMPLES = 2;
    public const CONTENTS_DOCUMENTS          = 3;
    public const CONTENTS_GIFTS              = 4;
    public const CONTENTS_RETURN_SHIPMENTS   = 5;

    protected $attributes = [
        'contents' => self::CONTENTS_COMMERCIAL_GOODS,
        'invoice'  => null,
        'items'    => CustomsDeclarationItemCollection::class,
        'weight'   => null,
    ];

    protected $casts      = [
        'contents' => 'int',
        'invoice'  => 'string',
        'items'    => CustomsDeclarationItemCollection::class,
        'weight'   => 'int',
    ];

    /**
     * Calculate weight automatically if it's not present.
     *
     * @return int
     * @noinspection PhpUnused
     */
    protected function getWeightAttribute(): int
    {
        return $this->attributes['weight'] ?? $this->items->reduce(static function (int $acc, $item) {
            return $acc + ($item['weight'] * $item['amount']);
        }, 0);
    }
}

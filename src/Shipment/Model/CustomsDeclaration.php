<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property int                                                                                   $contents
 * @property \MyParcelNL\Pdk\Base\Collection|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem $items
 * @property null|string                                                                           $invoice
 * @property int                                                                                   $weight - Readonly
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
        'items'    => Collection::class,
        'weight'   => null,
    ];

    protected $casts      = [
        'contents' => 'int',
        'invoice'  => 'string',
        'items'    => 'collection:' . CustomsDeclarationItem::class,
        'weight'   => 'int',
    ];

    /**
     * @param  \MyParcelNL\Sdk\src\Support\Collection|array[] $items
     *
     * @return self
     */
    public function setItemsAttribute($items): self
    {
        if (! $items instanceof Collection) {
            $items = new Collection($items);
        }

        $this->attributes['items'] = $items->mapInto(CustomsDeclarationItem::class);

        return $this;
    }

    /**
     * Calculate weight automatically if it's not present.
     *
     * @return int
     */
    protected function getWeightAttribute(): int
    {
        return $this->attributes['weight'] ?? $this->items->reduce(static function (int $acc, $item) {
            return $acc + ($item['weight'] * $item['amount']);
        }, 0);
    }
}

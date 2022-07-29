<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property CustomsDeclarationItem[] $items
 * @method CustomsDeclarationItem first(callable $callback = null, $default = null)
 * @method CustomsDeclarationItem last(callable $callback = null, $default = null)
 * @method CustomsDeclarationItem pop()
 * @method CustomsDeclarationItem shift()
 * @method CustomsDeclarationItem[] all()
 */
class CustomsDeclarationItemCollection extends Collection
{
    protected $cast = CustomsDeclarationItem::class;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;

/**
 * @property CustomsDeclarationItem[] $items
 */
class CustomsDeclarationItemCollection extends Collection
{
    protected $cast = CustomsDeclarationItem::class;
}

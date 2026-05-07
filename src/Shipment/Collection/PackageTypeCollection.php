<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\PackageType;

/**
 * @property PackageType[] $items
 */
final class PackageTypeCollection extends Collection
{
    protected $cast = PackageType::class;
}

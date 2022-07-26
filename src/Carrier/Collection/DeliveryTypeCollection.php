<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Collection;

use MyParcelNL\Pdk\Base\Collection;
use MyParcelNL\Pdk\Carrier\Model\Options\DeliveryType;

/**
 * @property \MyParcelNL\Pdk\Carrier\Model\Options\DeliveryType[] $items
 */
class DeliveryTypeCollection extends Collection
{
    protected $cast = DeliveryType::class;
}

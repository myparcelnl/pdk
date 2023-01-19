<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Collection;

use MyParcelNL\Pdk\Account\Model\ShopCarrierConfiguration;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property \MyParcelNL\Pdk\Account\Model\ShopCarrierConfiguration[] $items
 */
class ShopCarrierConfigurationCollection extends Collection
{
    protected $cast = ShopCarrierConfiguration::class;
}

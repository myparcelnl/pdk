<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;

/**
 * @property \MyParcelNL\Pdk\Settings\Model\CarrierSettings[] $items
 */
class CarrierSettingsCollection extends Collection
{
    protected $cast = CarrierSettings::class;
}

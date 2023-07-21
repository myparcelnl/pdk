<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property \MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel[] $items
 */
class SettingsModelCollection extends Collection
{
    /**
     * @var null|string
     */
    public $id;
}

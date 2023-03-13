<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Collection;

use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property \MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel[] $items
 */
class SettingsModelCollection extends Collection implements StorableArrayable
{
    /**
     * @var null|string
     */
    public $id;

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toStorableArray(): array
    {
        $array = [];

        foreach ($this->items as $key => $item) {
            if ($item instanceof StorableArrayable) {
                $array[$key] = $item->toStorableArray();
                continue;
            }

            $array[$key] = $item;
        }

        return $array;
    }
}

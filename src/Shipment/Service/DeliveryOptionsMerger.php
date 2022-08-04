<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Service;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

class DeliveryOptionsMerger
{
    /**
     * @param  array $options
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions
     */
    public static function create(array $options): DeliveryOptions
    {
        $data = (new Collection($options))
            ->filter()
            ->reduce(static function (array $acc, $current) {
                if ($current instanceof DeliveryOptions) {
                    $current = $current->toArray();
                }

                return Utils::mergeArraysIgnoringNull($acc, $current);
            }, []);

        return new DeliveryOptions($data);
    }
}

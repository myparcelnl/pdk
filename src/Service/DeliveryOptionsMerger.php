<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Service;

use MyParcelNL\Pdk\Base\Utils;
use MyParcelNL\Pdk\Shipment\Model\Options\DeliveryOptions;
use MyParcelNL\Sdk\src\Support\Collection;

class DeliveryOptionsMerger
{
    private const DEFAULT_VALUES = [
        'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
    ];

    /**
     * @param ...$options
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Options\DeliveryOptions
     * @throws \Exception
     */
    public static function create(...$options): DeliveryOptions
    {
        $previous = self::DEFAULT_VALUES;

        $adapters = (new Collection($options))
            ->filter()
            ->map(static function ($adapter) use (&$previous) {
                $previous = Utils::mergeValuesByKeys($previous, $adapter->toArray());

                return (new Collection($previous))->toArrayWithoutNull();
            })
            ->toArrayWithoutNull();

        return new DeliveryOptions(end($adapters));
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Service;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

class DeliveryOptionsMerger
{
    /**
     * @param  (array|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions|null)[] $options
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions
     * @throws \Exception
     */
    public static function create(array $options): DeliveryOptions
    {
        $data = (new Collection($options))
            ->filter()
            ->reduce(static function (array $acc, $current) {
                if ($current instanceof DeliveryOptions) {
                    $current = $current->toArray();
                }

                return self::mergeArraysIgnoringNull($acc, $current);
            }, []);

        return new DeliveryOptions($data);
    }

    /**
     * @param  array $previous
     * @param  array $current
     *
     * @return array
     */
    private static function mergeArraysIgnoringNull(array $previous, array $current): array
    {
        $keys = array_keys($current);

        foreach ($keys as $key) {
            if (is_array($current[$key])) {
                $current[$key] = self::mergeArraysIgnoringNull($previous[$key] ?? [], $current[$key]);
            }

            if (null !== $current[$key]) {
                continue;
            }

            $current[$key] = $previous[$key] ?? null;
        }

        return $current + $previous;
    }
}

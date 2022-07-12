<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Service;

use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\ShipmentOptionsV3Adapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\PickupLocationV3Adapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\DeliveryOptionsV3Adapter;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;

class DeliveryOptionsMerger
{
    private const DEFAULT_VALUES = [
        'deliveryType' => AbstractConsignment::DELIVERY_TYPE_STANDARD_NAME,
    ];

    /**
     * @param  \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter|array ...$options
     *
     * @throws \Exception
     */
    public static function create(...$options): AbstractDeliveryOptionsAdapter
    {
        $arr = [];
        foreach ($options as $option) {
            if (is_array($option)) {
                $arr[] = $option;
                continue;
            }

            if (is_a($option, AbstractDeliveryOptionsAdapter::class)) {
                $arr[] = $option->toArray();
                continue;
            }

            // todo show what $option is
            throw new \RuntimeException(var_export($option, true));
        }

        return DeliveryOptionsAdapterFactory::create(array_merge(self::DEFAULT_VALUES, ...$arr));
    }
}

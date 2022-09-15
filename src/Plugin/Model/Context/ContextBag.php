<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Plugin\Context;

/**
 * @property \MyParcelNL\Pdk\Plugin\Model\Context\GlobalContext                $global
 * @property null|\MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection $orderData
 * @property null|\MyParcelNL\Pdk\Plugin\Model\Context\DeliveryOptionsContext  $deliveryOptionsConfig
 */
class ContextBag extends Model
{
    public $attributes = [
        Context::ID_GLOBAL                  => null,
        Context::ID_ORDER_DATA              => null,
        Context::ID_DELIVERY_OPTIONS_CONFIG => null,
    ];

    public $casts      = [
        Context::ID_GLOBAL                  => GlobalContext::class,
        Context::ID_ORDER_DATA              => OrderDataContextCollection::class,
        Context::ID_DELIVERY_OPTIONS_CONFIG => DeliveryOptionsContext::class,
    ];
}

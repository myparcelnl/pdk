<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Context\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Context\Context;

/**
 * @property \MyParcelNL\Pdk\Context\Model\GlobalContext                        $global
 * @property null|\MyParcelNL\Pdk\Context\Model\DynamicContext                  $dynamic
 * @property null|\MyParcelNL\Pdk\Context\Collection\OrderDataContextCollection $orderData
 * @property null|\MyParcelNL\Pdk\Context\Model\PluginSettingsViewContext       $pluginSettingsView
 * @property null|\MyParcelNL\Pdk\Context\Model\ProductSettingsViewContext      $productSettingsView
 * @property null|\MyParcelNL\Pdk\Context\Model\CheckoutContext                 $deliveryOptions
 */
class ContextBag extends Model
{
    public $attributes = [
        Context::ID_GLOBAL                => null,
        Context::ID_DYNAMIC               => null,
        Context::ID_ORDER_DATA            => null,
        Context::ID_PLUGIN_SETTINGS_VIEW  => null,
        Context::ID_PRODUCT_SETTINGS_VIEW => null,
        Context::ID_CHECKOUT              => null,
    ];

    public $casts      = [
        Context::ID_GLOBAL                => GlobalContext::class,
        Context::ID_DYNAMIC               => DynamicContext::class,
        Context::ID_ORDER_DATA            => OrderDataContextCollection::class,
        Context::ID_PLUGIN_SETTINGS_VIEW  => PluginSettingsViewContext::class,
        Context::ID_PRODUCT_SETTINGS_VIEW => ProductSettingsViewContext::class,
        Context::ID_CHECKOUT              => CheckoutContext::class,
    ];
}

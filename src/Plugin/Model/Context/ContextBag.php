<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Plugin\Context;

/**
 * @property \MyParcelNL\Pdk\Plugin\Model\Context\GlobalContext                   $global
 * @property null|\MyParcelNL\Pdk\Plugin\Model\Context\DynamicContext             $dynamic
 * @property null|\MyParcelNL\Pdk\Plugin\Collection\OrderDataContextCollection    $orderData
 * @property null|\MyParcelNL\Pdk\Plugin\Model\Context\PluginSettingsViewContext  $pluginSettingsView
 * @property null|\MyParcelNL\Pdk\Plugin\Model\Context\ProductSettingsViewContext $productSettingsView
 * @property null|\MyParcelNL\Pdk\Plugin\Model\Context\CheckoutContext            $deliveryOptions
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

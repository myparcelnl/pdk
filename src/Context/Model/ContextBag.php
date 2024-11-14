<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Context\Collection\OrderDataContextCollection;
use MyParcelNL\Pdk\Context\Collection\ProductDataContextCollection;
use MyParcelNL\Pdk\Context\Context;

/**
 * @property \MyParcelNL\Pdk\Context\Model\GlobalContext                          $global
 * @property null|\MyParcelNL\Pdk\Context\Model\DynamicContext                    $dynamic
 * @property null|\MyParcelNL\Pdk\Context\Model\CheckoutContext                   $checkout
 * @property null|\MyParcelNL\Pdk\Context\Collection\OrderDataContextCollection   $orderData
 * @property null|\MyParcelNL\Pdk\Context\Model\PluginSettingsViewContext         $pluginSettingsView
 * @property null|\MyParcelNL\Pdk\Context\Collection\ProductDataContextCollection $productData
 * @property null|\MyParcelNL\Pdk\Context\Model\ProductSettingsViewContext        $productSettingsView
 */
class ContextBag extends Model
{
    public $attributes = [
        Context::ID_GLOBAL                => null,
        Context::ID_DYNAMIC               => null,
        Context::ID_CHECKOUT              => null,
        Context::ID_ORDER_DATA            => null,
        Context::ID_PLUGIN_SETTINGS_VIEW  => null,
        Context::ID_PRODUCT_DATA          => null,
        Context::ID_PRODUCT_SETTINGS_VIEW => null,
    ];

    public $casts      = [
        Context::ID_GLOBAL                => GlobalContext::class,
        Context::ID_DYNAMIC               => DynamicContext::class,
        Context::ID_CHECKOUT              => CheckoutContext::class,
        Context::ID_ORDER_DATA            => OrderDataContextCollection::class,
        Context::ID_PLUGIN_SETTINGS_VIEW  => PluginSettingsViewContext::class,
        Context::ID_PRODUCT_DATA          => ProductDataContextCollection::class,
        Context::ID_PRODUCT_SETTINGS_VIEW => ProductSettingsViewContext::class,
    ];

    public $lazy       = [
        Context::ID_GLOBAL,
        Context::ID_DYNAMIC,
        Context::ID_CHECKOUT,
        Context::ID_ORDER_DATA,
        Context::ID_PLUGIN_SETTINGS_VIEW,
        Context::ID_PRODUCT_DATA,
        Context::ID_PRODUCT_SETTINGS_VIEW,
    ];

    public function __construct(?array $data = null)
    {
        parent::__construct($data);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context;

final class Context
{
    public const ID_GLOBAL                = 'global';
    public const ID_DYNAMIC               = 'dynamic';
    public const ID_CHECKOUT              = 'checkout';
    public const ID_ORDER_DATA            = 'orderData';
    public const ID_PLUGIN_SETTINGS_VIEW  = 'pluginSettingsView';
    public const ID_PRODUCT_DATA          = 'productData';
    public const ID_PRODUCT_SETTINGS_VIEW = 'productSettingsView';
    public const ALL = [
        self::ID_GLOBAL,
        self::ID_DYNAMIC,
        self::ID_CHECKOUT,
        self::ID_ORDER_DATA,
        self::ID_PLUGIN_SETTINGS_VIEW,
        self::ID_PRODUCT_DATA,
        self::ID_PRODUCT_SETTINGS_VIEW,
    ];
}

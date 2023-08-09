<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Frontend\Service\AbstractViewService;

class MockAbstractViewService extends AbstractViewService
{
    public const PAGE_CHECKOUT        = 'checkout';
    public const PAGE_CHILD_PRODUCT   = 'child_product';
    public const PAGE_ORDER           = 'order';
    public const PAGE_ORDER_LIST      = 'order_list';
    public const PAGE_PLUGIN_SETTINGS = 'plugin_settings';
    public const PAGE_PRODUCT         = 'product';
    public const ALL_PDK_PAGES        = [
        self::PAGE_CHILD_PRODUCT,
        self::PAGE_ORDER,
        self::PAGE_ORDER_LIST,
        self::PAGE_PLUGIN_SETTINGS,
        self::PAGE_PRODUCT,
    ];

    public function isCheckoutPage(): bool
    {
        global $currentPage;

        return self::PAGE_CHECKOUT === $currentPage;
    }

    public function isChildProductPage(): bool
    {
        global $currentPage;

        return self::PAGE_CHILD_PRODUCT === $currentPage;
    }

    public function isOrderListPage(): bool
    {
        global $currentPage;

        return self::PAGE_ORDER_LIST === $currentPage;
    }

    public function isOrderPage(): bool
    {
        global $currentPage;

        return self::PAGE_ORDER === $currentPage;
    }

    public function isPluginSettingsPage(): bool
    {
        global $currentPage;

        return self::PAGE_PLUGIN_SETTINGS === $currentPage;
    }

    public function isProductPage(): bool
    {
        global $currentPage;

        return self::PAGE_PRODUCT === $currentPage;
    }
}

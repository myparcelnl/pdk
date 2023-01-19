<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Plugin\Service\AbstractViewService;

class MockAbstractViewService extends AbstractViewService
{
    public const PAGE_ORDER_LIST = 'order_list';
    public const PAGE_ORDER      = 'order';
    public const ALL_PDK_PAGES   = [
        self::PAGE_ORDER_LIST,
        self::PAGE_ORDER,
    ];

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
}

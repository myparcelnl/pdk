<?php

declare(strict_types=1);

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractViewService;

/**
 * Defines all pdk components and the views where they should render.
 *
 * @see \MyParcelNL\Pdk\Frontend\Contract\FrontendRenderServiceInterface
 * @see \MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface
 */
dataset('components', [
    'init script' => [
        'callback' => fn() => fn() => Frontend::renderInitScript(),
        'views'    => MockAbstractViewService::ALL_PDK_PAGES,
    ],

    'modals' => [
        'callback' => fn() => fn() => Frontend::renderModals(),
        'views'    => [
            MockAbstractViewService::PAGE_ORDER_LIST,
            MockAbstractViewService::PAGE_ORDER,
        ],
    ],

    'notifications' => [
        'callback' => fn() => fn() => Frontend::renderNotifications(),
        'views'    => MockAbstractViewService::ALL_PDK_PAGES,
    ],

    'order box' => [
        'callback' => fn() => fn() => Frontend::renderOrderBox(new PdkOrder(['externalIdentifier' => 'P00924872'])),
        'views'    => [MockAbstractViewService::PAGE_ORDER],
    ],

    'order list column' => [
        'callback' => fn() => fn() => Frontend::renderOrderListItem(
            new PdkOrder(['externalIdentifier' => 'P00924878'])
        ),
        'views'    => [MockAbstractViewService::PAGE_ORDER_LIST],
    ],

    'plugin settings' => [
        'callback' => fn() => fn() => Frontend::renderPluginSettings(),
        'views'    => [MockAbstractViewService::PAGE_PLUGIN_SETTINGS],
    ],

    'product settings' => [
        'callback' => fn() => fn() => Frontend::renderProductSettings(new PdkProduct()),
        'views'    => [MockAbstractViewService::PAGE_PRODUCT],
    ],

    'child product settings' => [
        'callback' => fn() => fn() => Frontend::renderChildProductSettings(
            new PdkProduct(['parent' => new PdkProduct()])
        ),
        'views'    => [MockAbstractViewService::PAGE_CHILD_PRODUCT],
    ],

    'delivery options' => [
        'callback' => fn() => fn() => Frontend::renderDeliveryOptions(new PdkCart()),
        'views'    => [MockAbstractViewService::PAGE_CHECKOUT],
    ],
]);

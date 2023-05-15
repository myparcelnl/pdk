<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractViewService;

/**
 * Defines all pdk components and the views where they should render.
 *
 * @see \MyParcelNL\Pdk\Plugin\Contract\FrontendRenderServiceInterface
 * @see \MyParcelNL\Pdk\Plugin\Contract\ViewServiceInterface
 */
dataset('components', [
    'init script' => [
        'callback' => function () {
            return function () {
                return Frontend::renderInitScript();
            };
        },
        'views'    => MockAbstractViewService::ALL_PDK_PAGES,
    ],

    'modals' => [
        'callback' => function () {
            return function () {
                return Frontend::renderModals();
            };
        },
        'views'    => [
            MockAbstractViewService::PAGE_ORDER_LIST,
            MockAbstractViewService::PAGE_ORDER,
        ],
    ],

    'notifications' => [
        'callback' => function () {
            return function () {
                return Frontend::renderNotifications();
            };
        },
        'views'    => MockAbstractViewService::ALL_PDK_PAGES,
    ],

    'order box' => [
        'callback' => function () {
            return function () {
                return Frontend::renderOrderBox(new PdkOrder(['externalIdentifier' => 'P00924872']));
            };
        },
        'views'    => [MockAbstractViewService::PAGE_ORDER],
    ],

    'order list column' => [
        'callback' => function () {
            return function () {
                return Frontend::renderOrderListItem(new PdkOrder(['externalIdentifier' => 'P00924878']));
            };
        },
        'views'    => [MockAbstractViewService::PAGE_ORDER_LIST],
    ],

    'plugin settings' => [
        'callback' => function () {
            return function () {
                return Frontend::renderPluginSettings();
            };
        },
        'views'    => [MockAbstractViewService::PAGE_PLUGIN_SETTINGS],
    ],

    'product settings' => [
        'callback' => function () {
            return function () {
                return Frontend::renderProductSettings(new PdkProduct());
            };
        },
        'views'    => [MockAbstractViewService::PAGE_PRODUCT],
    ],
]);

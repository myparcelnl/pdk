<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\RenderService;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAbstractViewService;

/**
 * Defines all pdk components and the views where they should render.
 *
 * @see \MyParcelNL\Pdk\Plugin\Service\RenderServiceInterface
 * @see \MyParcelNL\Pdk\Plugin\Service\ViewServiceInterface
 */
dataset('components', [
    'init script' => [
        'callback' => function () {
            return function () {
                return RenderService::renderInitScript();
            };
        },
        'views'    => MockAbstractViewService::ALL_PDK_PAGES,
    ],

    'modals' => [
        'callback' => function () {
            return function () {
                return RenderService::renderModals();
            };
        },
        'views'    => MockAbstractViewService::ALL_PDK_PAGES,
    ],

    'notifications' => [
        'callback' => function () {
            return function () {
                return RenderService::renderNotifications();
            };
        },
        'views'    => MockAbstractViewService::ALL_PDK_PAGES,
    ],

    'order card' => [
        'callback' => function () {
            return function () {
                return RenderService::renderOrderCard(new PdkOrder(['externalIdentifier' => 'P00924872']));
            };
        },
        'views'    => [MockAbstractViewService::PAGE_ORDER],
    ],

    'order list column' => [
        'callback' => function () {
            return function () {
                return RenderService::renderOrderListColumn(new PdkOrder(['externalIdentifier' => 'P00924878']));
            };
        },
        'views'    => [MockAbstractViewService::PAGE_ORDER_LIST],
    ],

    'plugin settings' => [
        'callback' => function () {
            return function () {
                return RenderService::renderPluginSettings();
            };
        },
        'views'    => [MockAbstractViewService::PAGE_PLUGIN_SETTINGS],
    ],

    'product settings' => [
        'callback' => function () {
            return function () {
                return RenderService::renderProductSettings(new PdkProduct());
            };
        },
        'views'    => [MockAbstractViewService::PAGE_PRODUCT],
    ],
]);

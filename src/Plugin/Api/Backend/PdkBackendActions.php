<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api\Backend;

use MyParcelNL\Pdk\Plugin\Api\Contract\PdkActionsInterface;
use MyParcelNL\Pdk\Plugin\Api\Shared\PdkSharedActions;

final class PdkBackendActions implements PdkActionsInterface
{
    // Account
    public const UPDATE_ACCOUNT = 'updateAccount';
    // Orders
    public const EXPORT_ORDERS = 'exportOrders';
    public const FETCH_ORDERS  = 'fetchOrders';
    public const PRINT_ORDERS  = 'printOrders';
    public const UPDATE_ORDERS = 'updateOrders';
    // Returns
    public const EXPORT_RETURN = 'exportReturn';
    // Shipments
    public const DELETE_SHIPMENTS = 'deleteShipments';
    public const PRINT_SHIPMENTS  = 'printShipments';
    public const UPDATE_SHIPMENTS = 'updateShipments';
    // Settings
    public const UPDATE_PLUGIN_SETTINGS  = 'updatePluginSettings';
    public const UPDATE_PRODUCT_SETTINGS = 'updateProductSettings';
    // Webhook
    public const CREATE_WEBHOOKS = 'createWebhooks';
    public const DELETE_WEBHOOKS = 'deleteWebhooks';
    public const FETCH_WEBHOOKS  = 'fetchWebhooks';
    // Optional actions
    public const UPDATE_TRACKING_NUMBER = 'updateTrackingNumber';

    /**
     * @var \MyParcelNL\Pdk\Plugin\Api\Shared\PdkSharedActions
     */
    private $sharedActions;

    public function __construct(PdkSharedActions $sharedActions) {
        $this->sharedActions = $sharedActions;
    }

    /**
     * @return string[]
     */
    public function getActions(): array
    {
        return $this->sharedActions->getActions() + [
                self::CREATE_WEBHOOKS,
                self::DELETE_SHIPMENTS,
                self::DELETE_WEBHOOKS,
                self::EXPORT_ORDERS,
                self::EXPORT_RETURN,
                self::FETCH_ORDERS,
                self::FETCH_WEBHOOKS,
                self::PRINT_ORDERS,
                self::PRINT_SHIPMENTS,
                self::UPDATE_ACCOUNT,
                self::UPDATE_ORDERS,
                self::UPDATE_PLUGIN_SETTINGS,
                self::UPDATE_PRODUCT_SETTINGS,
                self::UPDATE_SHIPMENTS,
        ];
    }
}

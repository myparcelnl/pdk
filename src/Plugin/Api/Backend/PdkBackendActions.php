<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api\Backend;

use MyParcelNL\Pdk\Plugin\Api\PdkActionsInterface;

final class PdkBackendActions implements PdkActionsInterface
{
    // Context
    public const FETCH_CONTEXT = 'fetchContext';
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
    public const FETCH_SHIPMENTS  = 'fetchShipments';
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
     * @return string[]
     */
    public function getActions(): array
    {
        return [
            self::CREATE_WEBHOOKS,
            self::DELETE_SHIPMENTS,
            self::DELETE_WEBHOOKS,
            self::EXPORT_ORDERS,
            self::EXPORT_RETURN,
            self::FETCH_CONTEXT,
            self::FETCH_ORDERS,
            self::FETCH_SHIPMENTS,
            self::FETCH_WEBHOOKS,
            self::PRINT_ORDERS,
            self::PRINT_SHIPMENTS,
            self::UPDATE_ACCOUNT,
            self::UPDATE_ORDERS,
            self::UPDATE_PLUGIN_SETTINGS,
            self::UPDATE_PRODUCT_SETTINGS,
        ];
    }
}

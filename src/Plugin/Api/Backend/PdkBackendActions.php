<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Api\Backend;

final class PdkBackendActions
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
}

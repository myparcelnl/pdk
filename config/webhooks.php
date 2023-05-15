<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Webhook\Hook\OrderStatusChangeWebhook;
use MyParcelNL\Pdk\App\Webhook\Hook\ShipmentLabelCreatedWebhook;
use MyParcelNL\Pdk\App\Webhook\Hook\ShipmentStatusChangeWebhook;
use MyParcelNL\Pdk\App\Webhook\Hook\ShopCarrierAccessibilityUpdatedWebhook;
use MyParcelNL\Pdk\App\Webhook\Hook\ShopCarrierConfigurationUpdatedWebhook;
use MyParcelNL\Pdk\App\Webhook\Hook\ShopUpdatedWebhook;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;

return [
    /**
     * Called when a shipment status changes.
     */
    WebhookSubscription::SHIPMENT_STATUS_CHANGE             => ShipmentStatusChangeWebhook::class,

    /**
     * Called when a shipment label is created.
     */
    WebhookSubscription::SHIPMENT_LABEL_CREATED             => ShipmentLabelCreatedWebhook::class,

    /**
     * Called when an order status changes.
     */
    WebhookSubscription::ORDER_STATUS_CHANGE                => OrderStatusChangeWebhook::class,

    /**
     * Called when the shop is updated.
     */
    WebhookSubscription::SHOP_UPDATED                       => ShopUpdatedWebhook::class,

    /**
     * Called when the carrier accessibility is updated, e.g. when a carrier is enabled or disabled.
     */
    WebhookSubscription::SHOP_CARRIER_ACCESSIBILITY_UPDATED => ShopCarrierAccessibilityUpdatedWebhook::class,

    /**
     * Called when the carrier configuration is updated, e.g. when a carrier's settings are changed.
     */
    WebhookSubscription::SHOP_CARRIER_CONFIGURATION_UPDATED => ShopCarrierConfigurationUpdatedWebhook::class,
];

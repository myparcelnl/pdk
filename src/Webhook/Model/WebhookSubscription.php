<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|int    $id
 * @property null|string $hook
 * @property null|string $url
 */
class WebhookSubscription extends Model
{
    /**
     * Called when a shipment status changes.
     */
    public const SHIPMENT_STATUS_CHANGE = 'shipment_status_change';
    /**
     * Called when a shipment label is created.
     */
    public const SHIPMENT_LABEL_CREATED = 'shipment_label_created';
    /**
     * Called when an order status changes.
     */
    public const ORDER_STATUS_CHANGE = 'order_status_change';
    /**
     * Called when the shop is updated.
     */
    public const SHOP_UPDATED = 'shop_updated';
    /**
     * Called when the carrier accessibility is updated, e.g. when a carrier is enabled or disabled.
     */
    public const SHOP_CARRIER_ACCESSIBILITY_UPDATED = 'shop_carrier_accessibility_updated';
    /**
     * Called when the carrier configuration is updated, e.g. when a carrier's settings are changed.
     */
    public const SHOP_CARRIER_CONFIGURATION_UPDATED = 'shop_carrier_configuration_updated';
    /**
     * Called when a subscription is created or updated.
     */
    public const SUBSCRIPTION_CREATED_OR_UPDATED = 'subscription_created_or_updated';
    /**
     * All possible hooks.
     */
    public const ALL = [
        self::SHIPMENT_STATUS_CHANGE,
        self::SHIPMENT_LABEL_CREATED,
        self::ORDER_STATUS_CHANGE,
        self::SHOP_UPDATED,
        self::SHOP_CARRIER_ACCESSIBILITY_UPDATED,
        self::SHOP_CARRIER_CONFIGURATION_UPDATED,
        self::SUBSCRIPTION_CREATED_OR_UPDATED,
    ];

    public    $attributes = [
        'id'   => null,
        'hook' => null,
        'url'  => null,
    ];

    protected $casts      = [
        'id'   => 'int',
        'hook' => 'string',
        'url'  => 'string',
    ];
}

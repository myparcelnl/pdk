<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Webhook\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $hook
 * @property string $url
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

    public    $attributes = [
        'hook' => null,
        'url'  => null,
    ];

    protected $casts      = [
        'hook' => 'string',
        'url'  => 'string',
    ];
}
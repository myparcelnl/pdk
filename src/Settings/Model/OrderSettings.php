<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $ignoreOrderStatuses
 * @property string $orderStatusMail
 * @property string $sendNotificationAfter
 * @property int    $sendOrderStateForDigitalStamps
 * @property string $statusOnLabelCreate
 * @property string $statusWhenDelivered
 * @property string $statusWhenLabelScanned
 */
class OrderSettings extends Model
{
    public const IGNORE_ORDER_STATUSES               = 'ignoreOrderStatuses';
    public const ORDER_STATUS_MAIL                   = 'orderStatusMail';
    public const SEND_NOTIFICATION_AFTER             = 'sendNotificationAfter';
    public const SEND_ORDER_STATE_FOR_DIGITAL_STAMPS = 'sendOrderStateForDigitalStamps';
    public const STATUS_ON_LABEL_CREATE              = 'statusOnLabelCreate';
    public const STATUS_WHEN_DELIVERED               = 'statusWhenDelivered';
    public const STATUS_WHEN_LABEL_SCANNED           = 'statusWhenLabelScanned';

    protected $attributes = [
        self::IGNORE_ORDER_STATUSES               => null,
        self::ORDER_STATUS_MAIL                   => null,
        self::SEND_NOTIFICATION_AFTER             => null,
        self::SEND_ORDER_STATE_FOR_DIGITAL_STAMPS => null,
        self::STATUS_ON_LABEL_CREATE              => null,
        self::STATUS_WHEN_DELIVERED               => null,
        self::STATUS_WHEN_LABEL_SCANNED           => null,
    ];

    protected $casts      = [
        self::IGNORE_ORDER_STATUSES               => 'string',
        self::ORDER_STATUS_MAIL                   => 'string',
        self::SEND_NOTIFICATION_AFTER             => 'string',
        self::SEND_ORDER_STATE_FOR_DIGITAL_STAMPS => 'integer',
        self::STATUS_ON_LABEL_CREATE              => 'string',
        self::STATUS_WHEN_DELIVERED               => 'string',
        self::STATUS_WHEN_LABEL_SCANNED           => 'string',
    ];
}

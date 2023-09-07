<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @property int         $emptyDigitalStampWeight
 * @property int         $emptyMailboxWeight
 * @property int         $emptyParcelWeight
 * @property bool        $orderStatusMail
 * @property bool        $saveCustomerAddress
 * @property string|null $sendNotificationAfter
 * @property bool        $sendOrderStateForDigitalStamps
 * @property string|null $statusOnLabelCreate
 * @property string|null $statusWhenDelivered
 * @property string|null $statusWhenLabelScanned
 */
class OrderSettings extends AbstractSettingsModel
{
    /**
     * Settings category ID.
     */
    public const ID = 'order';
    /**
     * Settings in this category.
     */
    public const EMPTY_DIGITAL_STAMP_WEIGHT         = 'emptyDigitalStampWeight';
    public const EMPTY_MAILBOX_WEIGHT               = 'emptyMailboxWeight';
    public const EMPTY_PARCEL_WEIGHT                = 'emptyParcelWeight';
    public const SAVE_CUSTOMER_ADDRESS              = 'saveCustomerAddress';
    public const SEND_ORDER_STATE_FOR_DIGITAL_STAMP = 'sendOrderStateForDigitalStamp';
    public const STATUS_ON_LABEL_CREATE             = 'statusOnLabelCreate';
    public const STATUS_WHEN_DELIVERED              = 'statusWhenDelivered';
    public const STATUS_WHEN_LABEL_SCANNED          = 'statusWhenLabelScanned';

    protected $attributes = [
        'id' => self::ID,

        self::EMPTY_DIGITAL_STAMP_WEIGHT         => 0,
        self::EMPTY_MAILBOX_WEIGHT               => 0,
        self::EMPTY_PARCEL_WEIGHT                => 0,
        self::SAVE_CUSTOMER_ADDRESS              => false,
        self::SEND_ORDER_STATE_FOR_DIGITAL_STAMP => true,
        self::STATUS_ON_LABEL_CREATE             => null,
        self::STATUS_WHEN_DELIVERED              => null,
        self::STATUS_WHEN_LABEL_SCANNED          => null,
    ];

    protected $casts      = [
        self::EMPTY_DIGITAL_STAMP_WEIGHT         => 'int',
        self::EMPTY_MAILBOX_WEIGHT               => 'int',
        self::EMPTY_PARCEL_WEIGHT                => 'int',
        self::SAVE_CUSTOMER_ADDRESS              => 'bool',
        self::SEND_ORDER_STATE_FOR_DIGITAL_STAMP => 'bool',
        self::STATUS_ON_LABEL_CREATE             => 'string',
        self::STATUS_WHEN_DELIVERED              => 'string',
        self::STATUS_WHEN_LABEL_SCANNED          => 'string',
    ];
}

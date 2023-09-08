<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * @property bool        $barcodeInNote
 * @property null|string $barcodeInNoteTitle
 * @property bool        $conceptShipments
 * @property int         $emptyDigitalStampWeight
 * @property int         $emptyMailboxWeight
 * @property int         $emptyParcelWeight
 * @property bool        $orderMode
 * @property int|bool    $orderStatusMail
 * @property bool        $processDirectly
 * @property bool        $saveCustomerAddress
 * @property string|null $sendNotificationAfter
 * @property bool        $sendReturnEmail
 * @property bool        $shareCustomerInformation
 * @property int|string  $statusOnLabelCreate
 * @property int|string  $statusWhenDelivered
 * @property int|string  $statusWhenLabelScanned
 * @property bool        $trackTraceInAccount
 * @property bool        $trackTraceInEmail
 */
final class OrderSettings extends AbstractSettingsModel
{
    /**
     * Settings category ID.
     */
    public const ID = 'order';
    /**
     * Settings in this category.
     */
    public const BARCODE_IN_NOTE            = 'barcodeInNote';
    public const BARCODE_IN_NOTE_TITLE      = 'barcodeInNoteTitle';
    public const CONCEPT_SHIPMENTS          = 'conceptShipments';
    public const EMPTY_DIGITAL_STAMP_WEIGHT = 'emptyDigitalStampWeight';
    public const EMPTY_MAILBOX_WEIGHT       = 'emptyMailboxWeight';
    public const EMPTY_PARCEL_WEIGHT        = 'emptyParcelWeight';
    public const ORDER_MODE                 = 'orderMode';
    public const PROCESS_DIRECTLY           = 'processDirectly';
    public const SAVE_CUSTOMER_ADDRESS      = 'saveCustomerAddress';
    public const SEND_NOTIFICATION_AFTER    = 'sendNotificationAfter';
    public const SEND_RETURN_EMAIL          = 'sendReturnEmail';
    public const SHARE_CUSTOMER_INFORMATION = 'shareCustomerInformation';
    public const STATUS_ON_LABEL_CREATE     = 'statusOnLabelCreate';
    public const STATUS_WHEN_DELIVERED      = 'statusWhenDelivered';
    public const STATUS_WHEN_LABEL_SCANNED  = 'statusWhenLabelScanned';
    public const TRACK_TRACE_IN_ACCOUNT     = 'trackTraceInAccount';
    public const TRACK_TRACE_IN_EMAIL       = 'trackTraceInEmail';

    protected $attributes = [
        'id' => self::ID,

        self::BARCODE_IN_NOTE            => false,
        self::BARCODE_IN_NOTE_TITLE      => null,
        self::CONCEPT_SHIPMENTS          => true,
        self::EMPTY_DIGITAL_STAMP_WEIGHT => 0,
        self::EMPTY_MAILBOX_WEIGHT       => 0,
        self::EMPTY_PARCEL_WEIGHT        => 0,
        self::ORDER_MODE                 => false,
        self::PROCESS_DIRECTLY           => false,
        self::SAVE_CUSTOMER_ADDRESS      => false,
        self::SEND_NOTIFICATION_AFTER    => Settings::OPTION_NONE,
        self::SEND_RETURN_EMAIL          => false,
        self::SHARE_CUSTOMER_INFORMATION => false,
        self::STATUS_ON_LABEL_CREATE     => Settings::OPTION_NONE,
        self::STATUS_WHEN_DELIVERED      => Settings::OPTION_NONE,
        self::STATUS_WHEN_LABEL_SCANNED  => Settings::OPTION_NONE,
        self::TRACK_TRACE_IN_ACCOUNT     => false,
        self::TRACK_TRACE_IN_EMAIL       => false,
    ];

    protected $casts      = [
        self::BARCODE_IN_NOTE            => 'bool',
        self::BARCODE_IN_NOTE_TITLE      => 'string',
        self::CONCEPT_SHIPMENTS          => 'bool',
        self::EMPTY_DIGITAL_STAMP_WEIGHT => 'int',
        self::EMPTY_MAILBOX_WEIGHT       => 'int',
        self::EMPTY_PARCEL_WEIGHT        => 'int',
        self::ORDER_MODE                 => 'bool',
        self::PROCESS_DIRECTLY           => 'bool',
        self::SAVE_CUSTOMER_ADDRESS      => 'bool',
        self::SEND_NOTIFICATION_AFTER    => TriStateService::TYPE_STRING,
        self::SEND_RETURN_EMAIL          => 'bool',
        self::SHARE_CUSTOMER_INFORMATION => 'bool',
        self::STATUS_ON_LABEL_CREATE     => TriStateService::TYPE_STRING,
        self::STATUS_WHEN_DELIVERED      => TriStateService::TYPE_STRING,
        self::STATUS_WHEN_LABEL_SCANNED  => TriStateService::TYPE_STRING,
        self::TRACK_TRACE_IN_ACCOUNT     => 'bool',
        self::TRACK_TRACE_IN_EMAIL       => 'bool',
    ];
}

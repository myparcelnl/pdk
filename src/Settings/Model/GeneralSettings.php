<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @property bool        $barcodeInNote
 * @property null|string $barcodeInNoteTitle
 * @property bool        $conceptShipments
 * @property null|string $exportWithAutomaticStatus
 * @property bool        $orderMode
 * @property bool        $processDirectly
 * @property bool        $shareCustomerInformation
 * @property bool        $trackTraceInAccount
 * @property bool        $trackTraceInEmail
 */
class GeneralSettings extends AbstractSettingsModel
{
    /**
     * Settings category ID.
     */
    public const ID = 'general';
    /**
     * Settings in this category.
     */
    public const BARCODE_IN_NOTE              = 'barcodeInNote';
    public const BARCODE_IN_NOTE_TITLE        = 'barcodeInNoteTitle';
    public const CONCEPT_SHIPMENTS            = 'conceptShipments';
    public const EXPORT_WITH_AUTOMATIC_STATUS = 'exportWithAutomaticStatus';
    public const ORDER_MODE                   = 'orderMode';
    public const PROCESS_DIRECTLY             = 'processDirectly';
    public const SHARE_CUSTOMER_INFORMATION   = 'shareCustomerInformation';
    public const TRACK_TRACE_IN_ACCOUNT       = 'trackTraceInAccount';
    public const TRACK_TRACE_IN_EMAIL         = 'trackTraceInEmail';

    protected $attributes = [
        'id' => self::ID,

        self::BARCODE_IN_NOTE              => false,
        self::BARCODE_IN_NOTE_TITLE        => null,
        self::CONCEPT_SHIPMENTS            => true,
        self::EXPORT_WITH_AUTOMATIC_STATUS => null,
        self::ORDER_MODE                   => false,
        self::PROCESS_DIRECTLY             => false,
        self::SHARE_CUSTOMER_INFORMATION   => false,
        self::TRACK_TRACE_IN_ACCOUNT       => false,
        self::TRACK_TRACE_IN_EMAIL         => false,
    ];

    protected $casts      = [
        self::BARCODE_IN_NOTE              => 'bool',
        self::BARCODE_IN_NOTE_TITLE        => 'string',
        self::CONCEPT_SHIPMENTS            => 'bool',
        self::EXPORT_WITH_AUTOMATIC_STATUS => 'string',
        self::ORDER_MODE                   => 'bool',
        self::PROCESS_DIRECTLY             => 'bool',
        self::SHARE_CUSTOMER_INFORMATION   => 'bool',
        self::TRACK_TRACE_IN_ACCOUNT       => 'bool',
        self::TRACK_TRACE_IN_EMAIL         => 'bool',
    ];
}

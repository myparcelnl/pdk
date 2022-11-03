<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string $apiKey
 * @property bool        $apiLogging
 * @property bool        $barcodeInNote
 * @property bool        $conceptShipments
 * @property null|string $exportWithAutomaticStatus
 * @property bool        $orderMode
 * @property bool        $processDirectly
 * @property bool        $shareCustomerInformation
 * @property bool        $trackTraceInAccount
 * @property bool        $trackTraceInEmail
 */
class GeneralSettings extends Model
{
    /**
     * Settings category ID.
     */
    public const ID = 'general';
    /**
     * Settings in this category.
     */
    public const API_KEY                      = 'apiKey';
    public const API_LOGGING                  = 'apiLogging';
    public const BARCODE_IN_NOTE              = 'barcodeInNote';
    public const CONCEPT_SHIPMENTS            = 'conceptShipments';
    public const EXPORT_WITH_AUTOMATIC_STATUS = 'exportWithAutomaticStatus';
    public const ORDER_MODE                   = 'orderMode';
    public const PROCESS_DIRECTLY             = 'processDirectly';
    public const SHARE_CUSTOMER_INFORMATION   = 'shareCustomerInformation';
    public const TRACK_TRACE_IN_ACCOUNT       = 'trackTraceInAccount';
    public const TRACK_TRACE_IN_EMAIL         = 'trackTraceInEmail';

    protected $attributes = [
        self::API_KEY                      => null,
        self::API_LOGGING                  => false,
        self::BARCODE_IN_NOTE              => false,
        self::CONCEPT_SHIPMENTS            => true,
        self::EXPORT_WITH_AUTOMATIC_STATUS => null,
        self::ORDER_MODE                   => false,
        self::PROCESS_DIRECTLY             => false,
        self::SHARE_CUSTOMER_INFORMATION   => false,
        self::TRACK_TRACE_IN_ACCOUNT       => false,
        self::TRACK_TRACE_IN_EMAIL         => false,
    ];

    protected $casts      = [
        self::API_KEY                      => 'string',
        self::API_LOGGING                  => 'bool',
        self::BARCODE_IN_NOTE              => 'bool',
        self::CONCEPT_SHIPMENTS            => 'bool',
        self::EXPORT_WITH_AUTOMATIC_STATUS => 'string',
        self::ORDER_MODE                   => 'bool',
        self::PROCESS_DIRECTLY             => 'bool',
        self::SHARE_CUSTOMER_INFORMATION   => 'bool',
        self::TRACK_TRACE_IN_ACCOUNT       => 'bool',
        self::TRACK_TRACE_IN_EMAIL         => 'bool',
    ];
}

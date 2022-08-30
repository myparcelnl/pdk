<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string $apiKey
 * @property bool        $barcodeInNote
 * @property bool        $conceptShipments
 * @property bool        $enableApiLogging
 * @property bool        $orderMode
 * @property null|string $priceType
 * @property bool        $processDirectly
 * @property bool        $shareCustomerInformation
 * @property bool        $showDeliveryDay
 * @property bool        $trackTraceEmail
 * @property bool        $trackTraceMyAccount
 * @property bool        $useSeparateAddressFields
 */
class GeneralSettings extends Model
{
    public const API_KEY                     = 'apiKey';
    public const BARCODE_IN_NOTE             = 'barcodeInNote';
    public const CONCEPT_SHIPMENTS           = 'conceptShipments';
    public const ENABLE_API_LOGGING          = 'enableApiLogging';
    public const ORDER_MODE                  = 'orderMode';
    public const PRICE_TYPE                  = 'priceType';
    public const PROCESS_DIRECTLY            = 'processDirectly';
    public const SHARE_CUSTOMER_INFORMATION  = 'shareCustomerInformation';
    public const SHOW_DELIVERY_DAY           = 'showDeliveryDay';
    public const TRACK_TRACE_EMAIL           = 'trackTraceEmail';
    public const TRACK_TRACE_MY_ACCOUNT      = 'trackTraceMyAccount';
    public const USE_SEPARATE_ADDRESS_FIELDS = 'useSeparateAddressFields';

    protected $attributes = [
        self::API_KEY                     => null,
        self::BARCODE_IN_NOTE             => false,
        self::CONCEPT_SHIPMENTS           => true,
        self::ENABLE_API_LOGGING          => false,
        self::ORDER_MODE                  => false,
        self::PRICE_TYPE                  => null,
        self::PROCESS_DIRECTLY            => false,
        self::SHARE_CUSTOMER_INFORMATION  => false,
        self::SHOW_DELIVERY_DAY           => false,
        self::TRACK_TRACE_EMAIL           => false,
        self::TRACK_TRACE_MY_ACCOUNT      => false,
        self::USE_SEPARATE_ADDRESS_FIELDS => false,
    ];

    protected $casts      = [
        self::API_KEY                     => 'string',
        self::BARCODE_IN_NOTE             => 'bool',
        self::CONCEPT_SHIPMENTS           => 'bool',
        self::ENABLE_API_LOGGING          => 'bool',
        self::ORDER_MODE                  => 'bool',
        self::PRICE_TYPE                  => 'string',
        self::PROCESS_DIRECTLY            => 'bool',
        self::SHARE_CUSTOMER_INFORMATION  => 'bool',
        self::SHOW_DELIVERY_DAY           => 'bool',
        self::TRACK_TRACE_EMAIL           => 'bool',
        self::TRACK_TRACE_MY_ACCOUNT      => 'bool',
        self::USE_SEPARATE_ADDRESS_FIELDS => 'bool',
    ];
}

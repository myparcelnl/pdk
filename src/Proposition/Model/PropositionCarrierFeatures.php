<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Shipment\Model\PackageType;

/**
 * This model defines the overall capabilities/features for a Carrier as returned by the proposition API.
 *
 * @property array $packageTypes The package types supported by the carrier.
 * @property array $deliveryTypes The delivery types supported by the carrier.
 * @property array $shipmentOptions The shipment options supported by the carrier.
 * @property array $metadata The metadata for the carrier, including features.
 *
 * @package MyParcelNL\Pdk\Proposition
 */
class PropositionCarrierFeatures extends Model
{
    /**
     * Package types
     */
    public const  PACKAGE_TYPE_PACKAGE_NAME        = 'PACKAGE';
    public const  PACKAGE_TYPE_MAILBOX_NAME        = 'MAILBOX';
    public const  PACKAGE_TYPE_UNFRANKED_NAME      = 'UNFRANKED';
    public const  PACKAGE_TYPE_DIGITAL_STAMP_NAME  = 'DIGITAL_STAMP';
    public const  PACKAGE_TYPE_PACKAGE_SMALL_NAME  = 'SMALL_PACKAGE';
    public const  PACKAGE_TYPE_PALLET_NAME         = 'PALLET';
    public const  PACKAGE_TYPE_LETTER_NAME         = 'LETTER';

    public const PACKAGE_TYPES = [
        self::PACKAGE_TYPE_PACKAGE_NAME,
        self::PACKAGE_TYPE_MAILBOX_NAME,
        self::PACKAGE_TYPE_UNFRANKED_NAME,
        self::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
        self::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
        self::PACKAGE_TYPE_PALLET_NAME,
        self::PACKAGE_TYPE_LETTER_NAME,
    ];

    /**
     * Delivery types
     */
    public const DELIVERY_TYPE_EVENING_NAME  = 'EVENING_DELIVERY';
    public const DELIVERY_TYPE_EXPRESS_NAME  = 'EXPRESS_DELIVERY';
    public const DELIVERY_TYPE_MORNING_NAME  = 'MORNING_DELIVERY';
    public const DELIVERY_TYPE_PICKUP_NAME   = 'PICKUP_DELIVERY';
    public const DELIVERY_TYPE_PICKUP_EXPRESS_NAME   = 'PICKUP_EXPRESS_DELIVERY';
    public const DELIVERY_TYPE_SAME_DAY_NAME = 'SAME_DAY_DELIVERY';
    public const DELIVERY_TYPE_STANDARD_NAME = 'STANDARD_DELIVERY';

    public const DELIVERY_TYPES = [
        self::DELIVERY_TYPE_EVENING_NAME,
        self::DELIVERY_TYPE_EXPRESS_NAME,
        self::DELIVERY_TYPE_MORNING_NAME,
        self::DELIVERY_TYPE_PICKUP_NAME,
        self::DELIVERY_TYPE_PICKUP_EXPRESS_NAME,
        self::DELIVERY_TYPE_SAME_DAY_NAME,
        self::DELIVERY_TYPE_STANDARD_NAME,
    ];

    /**
     * Shipment Options
     */
    public const SHIPMENT_OPTION_LABEL_DESCRIPTION_NAME = 'LABEL_DESCRIPTION';
    public const SHIPMENT_OPTION_INSURANCE_NAME         = 'INSURANCE';
    public const SHIPMENT_OPTION_AGE_CHECK_NAME         = 'AGE_CHECK';
    public const SHIPMENT_OPTION_DIRECT_RETURN_NAME     = 'RETURN';
    public const SHIPMENT_OPTION_HIDE_SENDER_NAME       = 'HIDE_SENDER';
    public const SHIPMENT_OPTION_LARGE_FORMAT_NAME      = 'LARGE_FORMAT';
    public const SHIPMENT_OPTION_ONLY_RECIPIENT_NAME    = 'ONLY_RECIPIENT';
    public const SHIPMENT_OPTION_RECEIPT_CODE_NAME      = 'RECEIPT_CODE';
    public const SHIPMENT_OPTION_SAME_DAY_DELIVERY_NAME = 'SAME_DAY_DELIVERY';
    public const SHIPMENT_OPTION_SATURDAY_DELIVERY_NAME = 'SATURDAY_DELIVERY';
    public const SHIPMENT_OPTION_MONDAY_DELIVERY_NAME   = 'MONDAY_DELIVERY';
    public const SHIPMENT_OPTION_SIGNATURE_NAME         = 'SIGNATURE';
    public const SHIPMENT_OPTION_TRACKED_NAME           = 'TRACKED';
    public const SHIPMENT_OPTION_COLLECT_NAME           = 'COLLECT';
    public const SHIPMENT_OPTION_FRESH_FOOD_NAME        = 'FRESH_FOOD';
    public const SHIPMENT_OPTION_FROZEN_NAME            = 'FROZEN';

    public const SHIPMENT_OPTIONS = [
        self::SHIPMENT_OPTION_LABEL_DESCRIPTION_NAME,
        self::SHIPMENT_OPTION_INSURANCE_NAME,
        self::SHIPMENT_OPTION_AGE_CHECK_NAME,
        self::SHIPMENT_OPTION_DIRECT_RETURN_NAME,
        self::SHIPMENT_OPTION_HIDE_SENDER_NAME,
        self::SHIPMENT_OPTION_LARGE_FORMAT_NAME,
        self::SHIPMENT_OPTION_ONLY_RECIPIENT_NAME,
        self::SHIPMENT_OPTION_RECEIPT_CODE_NAME,
        self::SHIPMENT_OPTION_SAME_DAY_DELIVERY_NAME,
        self::SHIPMENT_OPTION_SIGNATURE_NAME,
        self::SHIPMENT_OPTION_TRACKED_NAME,
        self::SHIPMENT_OPTION_COLLECT_NAME,
        self::SHIPMENT_OPTION_FRESH_FOOD_NAME,
        self::SHIPMENT_OPTION_FROZEN_NAME,
    ];

    protected $attributes = [
        'packageTypes' => null,
        'deliveryTypes' => null,
        'deliveryCountries' => null,
        'pickupCountries' => null,
        'shipmentOptions' => null,
        'metadata' => null
    ];

    /**
     * @todo convert to enums in the future (PHP 8.1+)
     */
    protected $casts = [
        'packageTypes' => 'array',
        'deliveryTypes' => 'array',
        'deliveryCountries' => 'array',
        'pickupCountries' => 'array',
        'shipmentOptions' => 'array',
        'metadata' => 'array'
    ];
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|\MyParcelNL\Pdk\Carrier\Model\CarrierOptions  $carrier
 * @property null|\DateTime                                     $date
 * @property null|string                                        $deliveryType
 * @property null|string                                        $packageType
 * @property \MyParcelNL\Pdk\Shipment\Model\ShipmentOptions     $shipmentOptions
 * @property null|\MyParcelNL\Pdk\Shipment\Model\RetailLocation $pickupLocation
 */
class DeliveryOptions extends Model
{
    public const DELIVERY_TYPE_MORNING_ID    = 1;
    public const DELIVERY_TYPE_MORNING_NAME  = 'morning';
    public const DELIVERY_TYPE_EVENING_ID    = 3;
    public const DELIVERY_TYPE_EVENING_NAME  = 'evening';
    public const DELIVERY_TYPE_STANDARD_ID   = 2;
    public const DELIVERY_TYPE_STANDARD_NAME = 'standard';
    public const DELIVERY_TYPE_PICKUP_ID     = 4;
    public const DELIVERY_TYPE_PICKUP_NAME   = 'pickup';
    /**
     * @var int[]
     */
    public const DELIVERY_TYPES_IDS = [
        self::DELIVERY_TYPE_MORNING_ID,
        self::DELIVERY_TYPE_STANDARD_ID,
        self::DELIVERY_TYPE_EVENING_ID,
        self::DELIVERY_TYPE_PICKUP_ID,
    ];
    /**
     * @var string[]
     */
    public const DELIVERY_TYPES_NAMES = [
        self::DELIVERY_TYPE_MORNING_NAME,
        self::DELIVERY_TYPE_STANDARD_NAME,
        self::DELIVERY_TYPE_EVENING_NAME,
        self::DELIVERY_TYPE_PICKUP_NAME,
    ];
    /**
     * @var array
     */
    public const DELIVERY_TYPES_NAMES_IDS_MAP    = [
        self::DELIVERY_TYPE_MORNING_NAME  => self::DELIVERY_TYPE_MORNING_ID,
        self::DELIVERY_TYPE_STANDARD_NAME => self::DELIVERY_TYPE_STANDARD_ID,
        self::DELIVERY_TYPE_EVENING_NAME  => self::DELIVERY_TYPE_EVENING_ID,
        self::DELIVERY_TYPE_PICKUP_NAME   => self::DELIVERY_TYPE_PICKUP_ID,
    ];
    public const DEFAULT_DELIVERY_TYPE_ID        = self::DELIVERY_TYPE_STANDARD_ID;
    public const DEFAULT_DELIVERY_TYPE_NAME      = self::DELIVERY_TYPE_STANDARD_NAME;
    public const PACKAGE_TYPE_PACKAGE_ID         = 1;
    public const PACKAGE_TYPE_MAILBOX_ID         = 2;
    public const PACKAGE_TYPE_LETTER_ID          = 3;
    public const PACKAGE_TYPE_DIGITAL_STAMP_ID   = 4;
    public const PACKAGE_TYPE_PACKAGE_NAME       = 'package';
    public const PACKAGE_TYPE_MAILBOX_NAME       = 'mailbox';
    public const PACKAGE_TYPE_LETTER_NAME        = 'letter';
    public const PACKAGE_TYPE_DIGITAL_STAMP_NAME = 'digital_stamp';
    public const PACKAGE_TYPES_IDS               = [
        self::PACKAGE_TYPE_PACKAGE_ID,
        self::PACKAGE_TYPE_MAILBOX_ID,
        self::PACKAGE_TYPE_LETTER_ID,
        self::PACKAGE_TYPE_DIGITAL_STAMP_ID,
    ];
    public const PACKAGE_TYPES_NAMES             = [
        self::PACKAGE_TYPE_PACKAGE_NAME,
        self::PACKAGE_TYPE_MAILBOX_NAME,
        self::PACKAGE_TYPE_LETTER_NAME,
        self::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
    ];
    public const PACKAGE_TYPES_NAMES_IDS_MAP     = [
        self::PACKAGE_TYPE_PACKAGE_NAME       => self::PACKAGE_TYPE_PACKAGE_ID,
        self::PACKAGE_TYPE_MAILBOX_NAME       => self::PACKAGE_TYPE_MAILBOX_ID,
        self::PACKAGE_TYPE_LETTER_NAME        => self::PACKAGE_TYPE_LETTER_ID,
        self::PACKAGE_TYPE_DIGITAL_STAMP_NAME => self::PACKAGE_TYPE_DIGITAL_STAMP_ID,
    ];
    public const DEFAULT_PACKAGE_TYPE_ID         = self::PACKAGE_TYPE_PACKAGE_ID;
    public const DEFAULT_PACKAGE_TYPE_NAME       = self::PACKAGE_TYPE_PACKAGE_NAME;

    protected $attributes = [
        'carrier'         => null,
        'date'            => null,
        'deliveryType'    => null,
        'packageType'     => self::PACKAGE_TYPE_PACKAGE_NAME,
        'pickupLocation'  => null,
        'shipmentOptions' => ShipmentOptions::class,
    ];

    protected $casts      = [
        'carrier'         => 'string',
        'date'            => DateTime::class,
        'deliveryType'    => 'string',
        'packageType'     => 'string',
        'pickupLocation'  => RetailLocation::class,
        'shipmentOptions' => ShipmentOptions::class,
    ];

    /**
     * @return bool
     */
    public function isPickup(): bool
    {
        return $this->deliveryType === self::DELIVERY_TYPE_PICKUP_NAME && $this->pickupLocation;
    }
}

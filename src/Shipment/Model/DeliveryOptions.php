<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Facade\Settings;

/**
 * @property null|string                                        $carrier
 * @property null|\DateTime                                     $date
 * @property null|string                                        $deliveryType
 * @property int                                                $labelAmount
 * @property null|string                                        $packageType
 * @property null|\MyParcelNL\Pdk\Shipment\Model\RetailLocation $pickupLocation
 * @property \MyParcelNL\Pdk\Shipment\Model\ShipmentOptions     $shipmentOptions
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
        'deliveryType'    => self::DEFAULT_DELIVERY_TYPE_NAME,
        'labelAmount'     => 1,
        'packageType'     => self::DEFAULT_PACKAGE_TYPE_NAME,
        'pickupLocation'  => null,
        'shipmentOptions' => ShipmentOptions::class,
    ];

    protected $casts      = [
        'carrier'         => 'string',
        'date'            => DateTime::class,
        'deliveryType'    => 'string',
        'labelAmount'     => 'int',
        'packageType'     => 'string',
        'pickupLocation'  => RetailLocation::class,
        'shipmentOptions' => ShipmentOptions::class,
    ];

    public function __construct(?array $data = null) {
        parent::__construct($data);
        $this->carrier = $this->carrier ?? Platform::get('defaultCarrier');
    }

    /**
     * @return null|string
     * @noinspection PhpUnused
     */
    public function getDateAsString(): ?string
    {
        return $this->date ? $this->date->format('Y-m-d H:i:s') : null;
    }

    /**
     * @return null|int
     * @noinspection PhpUnused
     */
    public function getDeliveryTypeId(): ?int
    {
        return $this->deliveryType && array_key_exists($this->deliveryType, self::DELIVERY_TYPES_NAMES_IDS_MAP)
            ? self::DELIVERY_TYPES_NAMES_IDS_MAP[$this->deliveryType]
            : null;
    }

    /**
     * @return null|int
     * @noinspection PhpUnused
     */
    public function getPackageTypeId(): ?int
    {
        return $this->packageType && array_key_exists($this->packageType, self::PACKAGE_TYPES_NAMES_IDS_MAP)
            ? self::PACKAGE_TYPES_NAMES_IDS_MAP[$this->packageType]
            : null;
    }

    /**
     * @return bool
     */
    public function isPickup(): bool
    {
        return $this->deliveryType === self::DELIVERY_TYPE_PICKUP_NAME && $this->pickupLocation;
    }
}

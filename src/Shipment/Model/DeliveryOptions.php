<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Platform;

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
    /**
     * Names
     */
    public const DELIVERY_TYPE = 'deliveryType';
    public const PACKAGE_TYPE  = 'packageType';
    /**
     * Values
     */
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
    public const DELIVERY_TYPES_NAMES_IDS_MAP = [
        self::DELIVERY_TYPE_MORNING_NAME  => self::DELIVERY_TYPE_MORNING_ID,
        self::DELIVERY_TYPE_STANDARD_NAME => self::DELIVERY_TYPE_STANDARD_ID,
        self::DELIVERY_TYPE_EVENING_NAME  => self::DELIVERY_TYPE_EVENING_ID,
        self::DELIVERY_TYPE_PICKUP_NAME   => self::DELIVERY_TYPE_PICKUP_ID,
    ];
    public const DEFAULT_DELIVERY_TYPE_ID     = self::DELIVERY_TYPE_STANDARD_ID;
    public const DEFAULT_DELIVERY_TYPE_NAME   = self::DELIVERY_TYPE_STANDARD_NAME;
    /**
     * Package types
     */
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
        'carrier'           => null,
        'date'              => null,
        'labelAmount'       => 1,
        'pickupLocation'    => null,
        'shipmentOptions'   => ShipmentOptions::class,
        self::DELIVERY_TYPE => self::DEFAULT_DELIVERY_TYPE_NAME,
        self::PACKAGE_TYPE  => self::DEFAULT_PACKAGE_TYPE_NAME,
    ];

    protected $casts      = [
        'carrier'           => 'string',
        'date'              => DateTime::class,
        'labelAmount'       => 'int',
        'pickupLocation'    => RetailLocation::class,
        'shipmentOptions'   => ShipmentOptions::class,
        self::DELIVERY_TYPE => 'string',
        self::PACKAGE_TYPE  => 'string',
    ];

    public function __construct(?array $data = null)
    {
        if ($data[self::DELIVERY_TYPE]) {
            $data[self::DELIVERY_TYPE] = Utils::convertToName(
                $data[self::DELIVERY_TYPE],
                self::DELIVERY_TYPES_NAMES_IDS_MAP
            );
        }

        if ($data[self::PACKAGE_TYPE]) {
            $data[self::PACKAGE_TYPE] = Utils::convertToName(
                $data[self::PACKAGE_TYPE],
                self::PACKAGE_TYPES_NAMES_IDS_MAP
            );
        }

        $data['carrier'] = $data['carrier'] ?? Platform::get('defaultCarrier');

        parent::__construct($data);
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
        return Utils::convertToId($this->deliveryType, self::DELIVERY_TYPES_NAMES_IDS_MAP);
    }

    /**
     * @return null|int
     * @noinspection PhpUnused
     */
    public function getPackageTypeId(): ?int
    {
        return Utils::convertToId($this->packageType, self::PACKAGE_TYPES_NAMES_IDS_MAP);
    }

    /**
     * @return bool
     */
    public function isPickup(): bool
    {
        return $this->deliveryType === self::DELIVERY_TYPE_PICKUP_NAME && $this->pickupLocation;
    }
}

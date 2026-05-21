<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use DateTimeInterface;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Concern\HasCarrierAttribute;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageType;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryType;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

/**
 * @property Carrier                $carrier
 * @property null|int               $contractId
 * @property null|DateTimeInterface $date
 * @property null|string            $deliveryType
 * @property int                    $labelAmount
 * @property null|string            $packageType
 * @property null|RetailLocation    $pickupLocation
 * @property ShipmentOptions        $shipmentOptions
 */
class DeliveryOptions extends Model
{
    use HasCarrierAttribute;

    /**
     * Attributes
     */
    public const CARRIER          = 'carrier';
    public const CONTRACT_ID      = 'contractId';
    public const DATE             = 'date';
    public const DELIVERY_TYPE    = 'deliveryType';
    public const LABEL_AMOUNT     = 'labelAmount';
    public const PACKAGE_TYPE     = 'packageType';
    public const PICKUP_LOCATION  = 'pickupLocation';
    public const SHIPMENT_OPTIONS = 'shipmentOptions';
    /**
     * Values
     */
    public const DELIVERY_TYPE_MORNING_ID         = RefTypesDeliveryType::MORNING;
    public const DELIVERY_TYPE_MORNING_NAME       = 'morning';
    public const DELIVERY_TYPE_EVENING_ID         = RefTypesDeliveryType::EVENING;
    public const DELIVERY_TYPE_EVENING_NAME       = 'evening';
    public const DELIVERY_TYPE_STANDARD_ID        = RefTypesDeliveryType::STANDARD;
    public const DELIVERY_TYPE_STANDARD_NAME      = 'standard';
    public const DELIVERY_TYPE_PICKUP_ID          = RefTypesDeliveryType::PICKUP;
    public const DELIVERY_TYPE_PICKUP_NAME        = 'pickup';
    public const DELIVERY_TYPE_EXPRESS_ID         = RefTypesDeliveryType::EXPRESS;
    public const DELIVERY_TYPE_EXPRESS_NAME       = 'express';
    public const DELIVERY_TYPE_SAME_DAY_ID        = RefTypesDeliveryType::SAME_DAY;
    public const DELIVERY_TYPE_SAME_DAY_NAME      = 'same_day';
    public const DELIVERY_TYPE_EARLY_MORNING_ID   = RefTypesDeliveryType::EARLY_MORNING;
    public const DELIVERY_TYPE_EARLY_MORNING_NAME = 'early_morning';

    /**
     * Delivery-option toggle names that are NOT delivery types in the
     * capabilities API (no V1 or V2 SDK enum entry). They exist purely as
     * carrier-settings toggles — used as SettingKey input to derive the
     * matching 'allow*' / 'price*' / 'priceDeliveryType*' attribute names.
     *
     *  - ALLOW_HOME:            consumer-side "allow home delivery" form toggle
     *                           (paired with the backend-side
     *                           CarrierSettings::DELIVERY_OPTIONS_ENABLED filter)
     *  - INTERNATIONAL_MAILBOX: mailbox shipments to non-local destinations
     *  - MONDAY / SATURDAY:     scheduling preferences ("deliver on Mondays")
     */
    public const DELIVERY_OPTION_ALLOW_HOME            = 'deliveryOptions';
    public const DELIVERY_OPTION_INTERNATIONAL_MAILBOX = 'internationalMailbox';
    public const DELIVERY_OPTION_MONDAY                = 'mondayDelivery';
    public const DELIVERY_OPTION_SATURDAY              = 'saturdayDelivery';

    /**
     * @var int[]
     */
    public const DELIVERY_TYPES_IDS = [
        self::DELIVERY_TYPE_MORNING_ID,
        self::DELIVERY_TYPE_STANDARD_ID,
        self::DELIVERY_TYPE_EVENING_ID,
        self::DELIVERY_TYPE_PICKUP_ID,
        self::DELIVERY_TYPE_EXPRESS_ID,
        self::DELIVERY_TYPE_SAME_DAY_ID,
        self::DELIVERY_TYPE_EARLY_MORNING_ID,
    ];
    /**
     * @var string[]
     */
    public const DELIVERY_TYPES_NAMES = [
        self::DELIVERY_TYPE_MORNING_NAME,
        self::DELIVERY_TYPE_STANDARD_NAME,
        self::DELIVERY_TYPE_EVENING_NAME,
        self::DELIVERY_TYPE_PICKUP_NAME,
        self::DELIVERY_TYPE_EXPRESS_NAME,
        self::DELIVERY_TYPE_SAME_DAY_NAME,
        self::DELIVERY_TYPE_EARLY_MORNING_NAME,
    ];
    /**
     * @var array
     */
    public const DELIVERY_TYPES_NAMES_IDS_MAP = [
        self::DELIVERY_TYPE_MORNING_NAME       => self::DELIVERY_TYPE_MORNING_ID,
        self::DELIVERY_TYPE_STANDARD_NAME      => self::DELIVERY_TYPE_STANDARD_ID,
        self::DELIVERY_TYPE_EVENING_NAME       => self::DELIVERY_TYPE_EVENING_ID,
        self::DELIVERY_TYPE_PICKUP_NAME        => self::DELIVERY_TYPE_PICKUP_ID,
        self::DELIVERY_TYPE_EXPRESS_NAME       => self::DELIVERY_TYPE_EXPRESS_ID,
        self::DELIVERY_TYPE_SAME_DAY_NAME      => self::DELIVERY_TYPE_SAME_DAY_ID,
        self::DELIVERY_TYPE_EARLY_MORNING_NAME => self::DELIVERY_TYPE_EARLY_MORNING_ID,
    ];

    public const DELIVERY_TYPES_V2_MAP = [
        self::DELIVERY_TYPE_MORNING_NAME       => RefTypesDeliveryTypeV2::MORNING,
        self::DELIVERY_TYPE_STANDARD_NAME      => RefTypesDeliveryTypeV2::STANDARD,
        self::DELIVERY_TYPE_EVENING_NAME       => RefTypesDeliveryTypeV2::EVENING,
        self::DELIVERY_TYPE_PICKUP_NAME        => RefTypesDeliveryTypeV2::PICKUP,
        self::DELIVERY_TYPE_EXPRESS_NAME       => RefTypesDeliveryTypeV2::EXPRESS,
        self::DELIVERY_TYPE_SAME_DAY_NAME      => RefTypesDeliveryTypeV2::SAME_DAY,
        self::DELIVERY_TYPE_EARLY_MORNING_NAME => RefTypesDeliveryTypeV2::EARLY_MORNING,
    ];

    public const DEFAULT_DELIVERY_TYPE_ID     = self::DELIVERY_TYPE_STANDARD_ID;
    public const DEFAULT_DELIVERY_TYPE_NAME   = self::DELIVERY_TYPE_STANDARD_NAME;

    /**
     * Package types
     */
    public const  PACKAGE_TYPE_PACKAGE_ID         = RefShipmentPackageType::PACKAGE;
    public const  PACKAGE_TYPE_MAILBOX_ID         = RefShipmentPackageType::MAILBOX;
    public const  PACKAGE_TYPE_LETTER_ID          = RefShipmentPackageType::UNFRANKED;
    public const  PACKAGE_TYPE_DIGITAL_STAMP_ID   = RefShipmentPackageType::DIGITAL_STAMP;
    public const  PACKAGE_TYPE_PACKAGE_SMALL_ID   = RefShipmentPackageType::SMALL_PACKAGE;
    public const  PACKAGE_TYPE_PALLET_ID          = RefShipmentPackageType::PALLET;
    public const  PACKAGE_TYPE_ENVELOPE_ID        = RefShipmentPackageType::ENVELOPE;
    public const  PACKAGE_TYPE_PACKAGE_NAME       = 'package';
    public const  PACKAGE_TYPE_MAILBOX_NAME       = 'mailbox';
    public const  PACKAGE_TYPE_LETTER_NAME        = 'letter';
    public const  PACKAGE_TYPE_DIGITAL_STAMP_NAME = 'digital_stamp';
    public const  PACKAGE_TYPE_PACKAGE_SMALL_NAME = 'package_small';
    public const  PACKAGE_TYPE_PALLET_NAME        = 'pallet';
    public const  PACKAGE_TYPE_ENVELOPE_NAME      = 'envelope';

    public const PACKAGE_TYPES_IDS = [
        self::PACKAGE_TYPE_PACKAGE_ID,
        self::PACKAGE_TYPE_MAILBOX_ID,
        self::PACKAGE_TYPE_LETTER_ID,
        self::PACKAGE_TYPE_DIGITAL_STAMP_ID,
        self::PACKAGE_TYPE_PACKAGE_SMALL_ID,
        self::PACKAGE_TYPE_PALLET_ID,
        self::PACKAGE_TYPE_ENVELOPE_ID,
    ];
    public const PACKAGE_TYPES_NAMES = [
        self::PACKAGE_TYPE_PACKAGE_NAME,
        self::PACKAGE_TYPE_MAILBOX_NAME,
        self::PACKAGE_TYPE_LETTER_NAME,
        self::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
        self::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
        self::PACKAGE_TYPE_PALLET_NAME,
        self::PACKAGE_TYPE_ENVELOPE_NAME,
    ];

    public const PACKAGE_TYPES_NAMES_IDS_MAP     = [
        self::PACKAGE_TYPE_PACKAGE_NAME       => self::PACKAGE_TYPE_PACKAGE_ID,
        self::PACKAGE_TYPE_MAILBOX_NAME       => self::PACKAGE_TYPE_MAILBOX_ID,
        self::PACKAGE_TYPE_LETTER_NAME        => self::PACKAGE_TYPE_LETTER_ID,
        self::PACKAGE_TYPE_DIGITAL_STAMP_NAME => self::PACKAGE_TYPE_DIGITAL_STAMP_ID,
        self::PACKAGE_TYPE_PACKAGE_SMALL_NAME => self::PACKAGE_TYPE_PACKAGE_SMALL_ID,
        self::PACKAGE_TYPE_PALLET_NAME        => self::PACKAGE_TYPE_PALLET_ID,
        self::PACKAGE_TYPE_ENVELOPE_NAME      => self::PACKAGE_TYPE_ENVELOPE_ID,
    ];

    public const PACKAGE_TYPES_V2_MAP = [
        self::PACKAGE_TYPE_PACKAGE_NAME       => RefShipmentPackageTypeV2::PACKAGE,
        self::PACKAGE_TYPE_MAILBOX_NAME       => RefShipmentPackageTypeV2::MAILBOX,
        self::PACKAGE_TYPE_LETTER_NAME        => RefShipmentPackageTypeV2::UNFRANKED,
        self::PACKAGE_TYPE_DIGITAL_STAMP_NAME => RefShipmentPackageTypeV2::DIGITAL_STAMP,
        self::PACKAGE_TYPE_PACKAGE_SMALL_NAME => RefShipmentPackageTypeV2::SMALL_PACKAGE,
        self::PACKAGE_TYPE_PALLET_NAME        => RefShipmentPackageTypeV2::PALLET,
        self::PACKAGE_TYPE_ENVELOPE_NAME      => RefShipmentPackageTypeV2::ENVELOPE,
    ];

    /**
     * Whether a V2 delivery type is supported by this PDK version.
     *
     * Backed by {@see self::DELIVERY_TYPES_V2_MAP} — a value is "supported"
     * when the PDK has a mapped legacy name (and therefore calculators and
     * UI labels) for it. Used at the boundary (capabilities proxy, carrier
     * serialization) so SDK enum values the PDK doesn't know about cannot
     * reach the admin or checkout.
     */
    public static function isDeliveryTypeSupported(string $v2DeliveryType): bool
    {
        return in_array($v2DeliveryType, self::DELIVERY_TYPES_V2_MAP, true);
    }

    /**
     * Whether a V2 package type is supported by this PDK version.
     *
     * Backed by {@see self::PACKAGE_TYPES_V2_MAP}; see
     * {@see self::isDeliveryTypeSupported()} for the rationale.
     */
    public static function isPackageTypeSupported(string $v2PackageType): bool
    {
        return in_array($v2PackageType, self::PACKAGE_TYPES_V2_MAP, true);
    }

    public const  DEFAULT_PACKAGE_TYPE_ID         = self::PACKAGE_TYPE_PACKAGE_ID;
    public const  DEFAULT_PACKAGE_TYPE_NAME       = self::PACKAGE_TYPE_PACKAGE_NAME;
    public const  DEFAULT_PACKAGE_TYPE_V2         = RefShipmentPackageTypeV2::PACKAGE;

    protected $attributes = [
        self::CARRIER          => null,
        self::CONTRACT_ID      => null,
        self::DATE             => null,
        self::LABEL_AMOUNT     => 1,
        self::PICKUP_LOCATION  => null,
        self::SHIPMENT_OPTIONS => ShipmentOptions::class,
        self::DELIVERY_TYPE    => self::DEFAULT_DELIVERY_TYPE_NAME,
        self::PACKAGE_TYPE     => self::DEFAULT_PACKAGE_TYPE_NAME,
    ];

    protected $casts      = [
        // Note: carrier attribute is not using the cast system as we do not want to store a carrier instance internally, only resolve it on demand via the getter (see getCarrierAttribute).
        self::CONTRACT_ID      => 'int',
        self::DATE             => DateTime::class,
        self::LABEL_AMOUNT     => 'int',
        self::PICKUP_LOCATION  => RetailLocation::class,
        self::SHIPMENT_OPTIONS => ShipmentOptions::class,
        self::DELIVERY_TYPE    => 'string',
        self::PACKAGE_TYPE     => 'string',
    ];

    public function __construct(?array $data = null)
    {
        if (isset($data[self::DELIVERY_TYPE])) {
            $data[self::DELIVERY_TYPE] = Utils::convertToName(
                $data[self::DELIVERY_TYPE],
                self::DELIVERY_TYPES_NAMES_IDS_MAP
            );
        }

        if (isset($data[self::PACKAGE_TYPE])) {
            $data[self::PACKAGE_TYPE] = Utils::convertToName(
                $data[self::PACKAGE_TYPE],
                self::PACKAGE_TYPES_NAMES_IDS_MAP
            );
        }

        // Normalize legacy carrier names (e.g. "postnl", "dhlforyou") to the new V2 identifiers.
        // The delivery options endpoint and JS/Vue checkout app still use the legacy format.
        if (isset($data[self::CARRIER]) && is_string($data[self::CARRIER])) {
            $legacyToNewMap = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);
            $data[self::CARRIER] = $legacyToNewMap[$data[self::CARRIER]] ?? $data[self::CARRIER];
        }

        parent::__construct($data);
    }

    /**
     * Construct a delivery options object from V2-style capabilities definitions
     *
     * This is needed as the capabilities endpoint only returns V2-style delivery and package type values, but the repository and model use the old values for backwards compatibility (as they are also used for non-capabilities-based deliveries).
     *
     * @param array $data
     * @return DeliveryOptions
     */
    public static function fromCapabilitiesDefinitions(array $data): self
    {
        // Map delivery type
        $data[self::DELIVERY_TYPE] = array_flip(self::DELIVERY_TYPES_V2_MAP)[$data[self::DELIVERY_TYPE]] ?? $data[self::DELIVERY_TYPE];

        // Map package type
        $data[self::PACKAGE_TYPE] = array_flip(self::PACKAGE_TYPES_V2_MAP)[$data[self::PACKAGE_TYPE]] ?? $data[self::PACKAGE_TYPE];

        // We don't map carrier names here - they need to be converted when writing to the API as the repository here only handles the new UPPER_CASE variants.

        // Map shipment options via ShipmentOptions::fromCapabilitiesDefinitions (which uses individual Definition classes)
        $data[self::SHIPMENT_OPTIONS] = ShipmentOptions::fromCapabilitiesDefinitions($data[self::SHIPMENT_OPTIONS]);

        return new self($data);
    }

    /**
     * An effective inverse of "fromCapabilitiesDefinitions": returns an array representation of the model where the package type, delivery type and shipment options are mapped to V2 definitions
     * @param DeliveryOptions $deliveryOptions
     * @return array
     */
    public static function toCapabilitiesDefinitions(self $deliveryOptions): array
    {
        return \array_merge($deliveryOptions->toArrayWithoutNull(), [
            self::DELIVERY_TYPE => self::DELIVERY_TYPES_V2_MAP[$deliveryOptions->deliveryType] ?? $deliveryOptions->deliveryType,
            self::PACKAGE_TYPE  => self::PACKAGE_TYPES_V2_MAP[$deliveryOptions->packageType] ?? $deliveryOptions->packageType,
            self::SHIPMENT_OPTIONS => ShipmentOptions::toCapabilitiesDefinitions($deliveryOptions->shipmentOptions),
        ]);
    }

    /**
     * @return null|string
     * @noinspection PhpUnused
     */
    public function getDateAsString(): ?string
    {
        return $this->date ? $this->date->format(Pdk::get('defaultDateFormat')) : null;
    }

    /**
     * Always resolves a fresh Carrier from the repository so capability data is never stale.
     *
     * - No carrier set → returns the shop's default carrier; throws when none is available.
     * - Carrier name set but not found in the repository → throws (repository findOrFail).
     *
     * Reads directly from $this->attributes to avoid re-entering getAttribute() which would
     * cause infinite recursion (getAttribute → transformModelValue → mutateAttribute → here).
     *
     * @return \MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function getCarrierAttribute(): Carrier
    {
        $carrierName = $this->attributes[self::CARRIER];

        if (! $carrierName) {
            return Shop::getDefaultCarrierOrThrow();
        }

        return Pdk::get(CarrierRepositoryInterface::class)->findOrFail($carrierName);
    }

    /**
     * @return null|\DateTime
     */
    public function getDateAttribute(): ?DateTimeInterface
    {
        $date = $this->getCastAttributeValue(self::DATE);

        if (! $date || $date < new DateTime('now')) {
            return null;
        }

        return $date;
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

    /**
     * @param  null|int $flags
     *
     * @return array
     */
    public function toArray(?int $flags = null): array
    {
        // The default toArray() path resolves the carrier via the getter so downstream consumers
        // (JS-PDK admin/checkout) receive the full Carrier model — including capabilities and
        // options — in serialized form. The getter throws when no carrier is stored AND no shop
        // default exists; detect that case upfront and emit the raw null attribute instead of
        // throwing during serialization.
        $hasStoredCarrier = (bool) ($this->attributes[self::CARRIER] ?? null);
        $shop             = AccountSettings::getShop();
        $hasShopDefault   = $shop && $shop->defaultCarrierModel;

        if (! $hasStoredCarrier && ! $hasShopDefault) {
            $array = $this->except(self::CARRIER, $flags);

            // Honour SKIP_NULL (toArrayWithoutNull / toStorableArray / ENCODED): omit the key
            // entirely instead of emitting carrier: null, which would otherwise leak past the
            // null-stripping serializers.
            if (! ($flags & Arrayable::SKIP_NULL)) {
                $array[self::CARRIER] = null;
            }

            return Utils::filterNull([self::DATE => $this->getDateAsString()]) + $array;
        }

        return Utils::filterNull([self::DATE => $this->getDateAsString()]) + parent::toArray($flags);
    }

    public function toStorableArray(): array
    {
        $array = parent::toStorableArray();
        // Carrier should be the (raw) name only, not the full resolved carrier data.
        $array[self::CARRIER] = $this->attributes[self::CARRIER] ?? null;
        return $array;
    }
}

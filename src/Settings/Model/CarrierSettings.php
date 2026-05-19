<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Base\Concern\ResolvesOptionAttributes;
use MyParcelNL\Pdk\Base\Support\SettingKey;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

/**
 * @property string               $id
 * @property bool                 $allowDeliveryOptions
 * @property bool                 $allowStandardDelivery
 * @property bool                 $allowEveningDelivery
 * @property bool                 $allowMondayDelivery
 * @property bool                 $allowMorningDelivery
 * @property bool                 $allowOnlyRecipient
 * @property bool                 $allowPickupLocations
 * @property bool                 $allowPriorityDelivery
 * @property bool                 $allowSameDayDelivery
 * @property bool                 $allowSaturdayDelivery
 * @property bool                 $allowSignature
 * @property bool                 $allowCollect
 * @property string               $cutoffTime
 * @property string               $cutoffTimeSameDay
 * @property string               $defaultPackageType
 * @property int                  $deliveryDaysWindow
 * @property string               $deliveryOptionsCustomCss
 * @property bool                 $deliveryOptionsEnabled
 * @property bool                 $deliveryOptionsEnabledForBackorders
 * @property int                  $digitalStampDefaultWeight
 * @property int                  $dropOffDelay
 * @property DropOffPossibilities $dropOffPossibilities
 * @property int<-1|0|1>          $exportAgeCheck
 * @property int<-1|0|1>          $exportHideSender
 * @property int<-1|0|1>          $exportInsurance
 * @property int<-1|0|1>          $exportLargeFormat
 * @property int<-1|0|1>          $exportOnlyRecipient
 * @property int<-1|0|1>          $exportReceiptCode
 * @property int<-1|0|1>          $exportReturn
 * @property int<-1|0|1>          $exportReturnLargeFormat
 * @property int<-1|0|1>          $exportSignature
 * @property int<-1|0|1>          $exportTracked
 * @property int<-1|0|1>          $exportCollect
 * @property int<-1|0|1>          $exportFreshFood
 * @property int<-1|0|1>          $exportFrozen
 * @property int<-1|0|1>          $exportPriorityDelivery
 * @property int                  $exportInsuranceFromAmount
 * @property int                  $exportInsurancePricePercentage
 * @property int                  $exportInsuranceUpTo
 * @property int                  $exportInsuranceUpToEu
 * @property int                  $exportInsuranceUpToRow
 * @property int                  $exportInsuranceUpToUnique
 * @property string               $exportReturnPackageType
 * @property float                $priceDeliveryTypeEvening
 * @property float                $priceDeliveryTypeMonday
 * @property float                $priceDeliveryTypeMorning
 * @property float                $priceDeliveryTypePickup
 * @property float                $priceDeliveryTypeSameDay
 * @property float                $priceDeliveryTypeSaturday
 * @property float                $priceDeliveryTypeStandard
 * @property float                $priceOnlyRecipient
 * @property float                $pricePackageTypeDigitalStamp
 * @property float                $pricePackageTypeMailbox
 * @property float                $priceSignature
 * @property float                $pricePriorityDelivery
 * @property float                $allowInternationalMailbox
 * @property float                $priceInternationalMailbox
 * @property float                $priceCollect
 */
class CarrierSettings extends AbstractSettingsModel
{
    use ResolvesOptionAttributes;

    /**
     * Settings category ID.
     */
    public const ID           = 'carrier';
    public const CARRIER_NAME = 'carrierName';
    /**
     * Settings in this category.
     * @TODO these settings need to be made generic based on available definitions in API spec
     */
    public const  CUTOFF_TIME                             = 'cutoffTime';
    public const  CUTOFF_TIME_SAME_DAY                    = 'cutoffTimeSameDay';
    public const  DEFAULT_PACKAGE_TYPE                    = 'defaultPackageType';
    public const  DELIVERY_DAYS_WINDOW                    = 'deliveryDaysWindow';
    public const  DELIVERY_OPTIONS_CUSTOM_CSS             = 'deliveryOptionsCustomCss';
    public const  DELIVERY_OPTIONS_ENABLED                = 'deliveryOptionsEnabled';
    public const  DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS = 'deliveryOptionsEnabledForBackorders';
    public const  DIGITAL_STAMP_DEFAULT_WEIGHT            = 'digitalStampDefaultWeight';
    public const  DROP_OFF_DELAY                          = 'dropOffDelay';
    public const  DROP_OFF_POSSIBILITIES                  = 'dropOffPossibilities';
    public const  EXPORT_INSURANCE_FROM_AMOUNT            = 'exportInsuranceFromAmount';
    public const  EXPORT_INSURANCE_PRICE_PERCENTAGE       = 'exportInsurancePricePercentage';
    public const  EXPORT_INSURANCE_UP_TO                  = 'exportInsuranceUpTo';
    public const  EXPORT_INSURANCE_UP_TO_EU               = 'exportInsuranceUpToEu';
    public const  EXPORT_INSURANCE_UP_TO_ROW              = 'exportInsuranceUpToRow';
    public const  EXPORT_INSURANCE_UP_TO_UNIQUE           = 'exportInsuranceUpToUnique';
    public const  EXPORT_RETURN_LARGE_FORMAT              = 'exportReturnLargeFormat';
    public const  EXPORT_RETURN_PACKAGE_TYPE              = 'exportReturnPackageType';


    protected $attributes = [
        'id'               => self::ID,
        self::CARRIER_NAME => null,

        self::CUTOFF_TIME                             => '16:00',
        self::CUTOFF_TIME_SAME_DAY                    => '10:00',
        self::DEFAULT_PACKAGE_TYPE                    => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
        self::DELIVERY_DAYS_WINDOW                    => 7,
        self::DELIVERY_OPTIONS_ENABLED                => false,
        self::DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS => false,
        self::DIGITAL_STAMP_DEFAULT_WEIGHT            => 0,
        self::DROP_OFF_DELAY                          => 0,
        self::DROP_OFF_POSSIBILITIES                  => DropOffPossibilities::class,
        self::EXPORT_INSURANCE_FROM_AMOUNT            => 0,
        self::EXPORT_INSURANCE_PRICE_PERCENTAGE       => 100,
        self::EXPORT_INSURANCE_UP_TO                  => 0,
        self::EXPORT_INSURANCE_UP_TO_EU               => 0,
        self::EXPORT_INSURANCE_UP_TO_ROW              => 0,
        self::EXPORT_INSURANCE_UP_TO_UNIQUE           => 0,
        self::EXPORT_RETURN_LARGE_FORMAT              => -1,
        self::EXPORT_RETURN_PACKAGE_TYPE              => DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME,
    ];

    protected $casts      = [
        self::CARRIER_NAME => 'string',

        self::CUTOFF_TIME                             => 'string',
        self::CUTOFF_TIME_SAME_DAY                    => 'string',
        self::DEFAULT_PACKAGE_TYPE                    => 'string',
        self::DELIVERY_DAYS_WINDOW                    => 'int',
        self::DELIVERY_OPTIONS_CUSTOM_CSS             => 'string',
        self::DELIVERY_OPTIONS_ENABLED                => 'bool',
        self::DELIVERY_OPTIONS_ENABLED_FOR_BACKORDERS => 'bool',
        self::DIGITAL_STAMP_DEFAULT_WEIGHT            => 'int',
        self::DROP_OFF_DELAY                          => 'int',
        self::DROP_OFF_POSSIBILITIES                  => DropOffPossibilities::class,
        self::EXPORT_INSURANCE_FROM_AMOUNT            => 'int',
        self::EXPORT_INSURANCE_PRICE_PERCENTAGE       => 'float',
        self::EXPORT_INSURANCE_UP_TO                  => 'int',
        self::EXPORT_INSURANCE_UP_TO_EU               => 'int',
        self::EXPORT_INSURANCE_UP_TO_ROW              => 'int',
        self::EXPORT_INSURANCE_UP_TO_UNIQUE           => 'int',
        self::EXPORT_RETURN_LARGE_FORMAT              => TriStateService::TYPE_STRICT,
        self::EXPORT_RETURN_PACKAGE_TYPE              => 'string',
    ];

    /**
     * Populate attributes and casts dynamically from a handful of source schemas
     * (option definitions, delivery types, package types, international-mailbox).
     * Each source is one concern; merged here in declaration order. Static-class
     * literals in $this->attributes / $this->casts win on collision via array_merge.
     */
    protected function initializeResolvesOptionAttributes(): void
    {
        $dynamicAttributes = [];
        $dynamicCasts      = [];

        $sources = [
            $this->resolveDefinitionExportSchema(),
            $this->resolveDefinitionAllowSchema(),
            $this->resolveDefinitionPriceSchema(),
            $this->resolveDeliveryTypeAllowSchema(),
            $this->resolveDeliveryTypePriceSchema(),
            $this->resolvePackageTypePriceSchema(),
            $this->resolveInternationalMailboxSchema(),
        ];

        foreach ($sources as [$attributes, $casts]) {
            $dynamicAttributes = array_merge($dynamicAttributes, $attributes);
            $dynamicCasts      = array_merge($dynamicCasts, $casts);
        }

        $this->attributes = array_merge($dynamicAttributes, $this->attributes);
        $this->casts      = array_merge($dynamicCasts, $this->casts);
    }

    /**
     * Export-settings schema (e.g. exportSignature): merchant-controlled default
     * applied when exporting a shipment. Cast is definition-specific (tri-state
     * for most options, 'int' for insurance, etc.).
     *
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function resolveDefinitionExportSchema(): array
    {
        return $this->resolveOptionAttributes(
            static function (OrderOptionDefinitionInterface $definition): ?string {
                return $definition->getCarrierSettingsKey();
            },
            TriStateService::INHERIT,
            static function (OrderOptionDefinitionInterface $definition): string {
                return $definition->getShipmentOptionsCast();
            }
        );
    }

    /**
     * Definition-derived allow-settings schema (e.g. allowSignature): whether the
     * consumer can toggle this shipment option at checkout. Always boolean.
     *
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function resolveDefinitionAllowSchema(): array
    {
        return $this->resolveOptionAttributes(
            static function (OrderOptionDefinitionInterface $definition): ?string {
                return $definition->getAllowSettingsKey();
            },
            false,
            static function (): string {
                return 'bool';
            }
        );
    }

    /**
     * Definition-derived price-settings schema (e.g. priceSignature): surcharge
     * added to shipping cost when the shipment option is active. Always float.
     *
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function resolveDefinitionPriceSchema(): array
    {
        return $this->resolveOptionAttributes(
            static function (OrderOptionDefinitionInterface $definition): ?string {
                return $definition->getPriceSettingsKey();
            },
            0,
            static function (): string {
                return 'float';
            }
        );
    }

    /**
     * Delivery-type allow-attrs (e.g. allowEveningDelivery). Auto-derived from
     * the SDK V2 enum, filtered to PDK-supported types via
     * {@see DeliveryOptions::isDeliveryTypeSupported()}, plus PDK-only consts
     * for delivery options that have no SDK counterpart (Monday delivery and
     * the master home-delivery toggle).
     *
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function resolveDeliveryTypeAllowSchema(): array
    {
        $types = array_values(array_filter(
            RefTypesDeliveryTypeV2::getAllowableEnumValues(),
            static function (string $v2): bool {
                return DeliveryOptions::isDeliveryTypeSupported($v2);
            }
        ));
        $types[] = DeliveryOptions::DELIVERY_OPTION_ALLOW_HOME;
        $types[] = DeliveryOptions::DELIVERY_OPTION_MONDAY;

        $attributes = [];
        $casts      = [];

        foreach ($types as $type) {
            $key              = SettingKey::allow($type);
            $attributes[$key] = false;
            $casts[$key]      = 'bool';
        }

        return [$attributes, $casts];
    }

    /**
     * Delivery-type price-attrs (e.g. priceDeliveryTypeEvening). Same auto-
     * derivation as the allow-schema, plus PDK-only consts for Monday and
     * Saturday (no SDK counterpart for either).
     *
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function resolveDeliveryTypePriceSchema(): array
    {
        $types = array_values(array_filter(
            RefTypesDeliveryTypeV2::getAllowableEnumValues(),
            static function (string $v2): bool {
                return DeliveryOptions::isDeliveryTypeSupported($v2);
            }
        ));
        $types[] = DeliveryOptions::DELIVERY_OPTION_MONDAY;
        $types[] = DeliveryOptions::DELIVERY_OPTION_SATURDAY;

        $attributes = [];
        $casts      = [];

        foreach ($types as $type) {
            $key              = SettingKey::priceDeliveryType($type);
            $attributes[$key] = 0;
            $casts[$key]      = 'float';
        }

        return [$attributes, $casts];
    }

    /**
     * Package-type price-attrs (e.g. pricePackageTypeMailbox). Auto-derived
     * from the SDK V2 enum, filtered to PDK-supported types via
     * {@see DeliveryOptions::isPackageTypeSupported()}. The legacy
     * 'SMALL_PACKAGE' ↔ 'packageSmall' asymmetry is absorbed by SettingKey's
     * PRICE_PACKAGE_TYPE_EXCEPTIONS map.
     *
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function resolvePackageTypePriceSchema(): array
    {
        // Default package type's price is the carrier's basePrice, not a
        // surcharge. Excluded to avoid stacking semantics.
        $types = array_filter(
            RefShipmentPackageTypeV2::getAllowableEnumValues(),
            static function (string $v2): bool {
                return DeliveryOptions::isPackageTypeSupported($v2)
                    && $v2 !== DeliveryOptions::DEFAULT_PACKAGE_TYPE_V2;
            }
        );

        $attributes = [];
        $casts      = [];

        foreach ($types as $type) {
            $key              = SettingKey::pricePackageType($type);
            $attributes[$key] = 0;
            $casts[$key]      = 'float';
        }

        return [$attributes, $casts];
    }

    /**
     * International-mailbox consumer-toggle + surcharge pair. Not a delivery
     * type — surfaces in CapabilitiesPackageTypeCalculator to gate mailbox
     * shipments to non-local destinations, and in DeliveryOptionsService to
     * override the regular mailbox price for those shipments.
     *
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function resolveInternationalMailboxSchema(): array
    {
        $allowKey = SettingKey::allow(DeliveryOptions::DELIVERY_OPTION_INTERNATIONAL_MAILBOX);
        $priceKey = SettingKey::price(DeliveryOptions::DELIVERY_OPTION_INTERNATIONAL_MAILBOX);

        return [
            [
                $allowKey => false,
                $priceKey => 0,
            ],
            [
                $allowKey => 'bool',
                $priceKey => 'float',
            ],
        ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return self
     */
    public static function fromCarrier(Carrier $carrier): self
    {
        /** @var null|\MyParcelNL\Pdk\Settings\Model\CarrierSettings $settings */
        $settings = Settings::all()->carrier->get($carrier->carrier);

        if (! $settings) {
            return new CarrierSettings();
        }

        return clone $settings;
    }
}

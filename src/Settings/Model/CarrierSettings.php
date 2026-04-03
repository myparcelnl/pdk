<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Base\Concern\ResolvesOptionAttributes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

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
    public const  ALLOW_DELIVERY_OPTIONS                  = 'allowDeliveryOptions';
    public const  ALLOW_STANDARD_DELIVERY                 = 'allowStandardDelivery';
    public const  ALLOW_EVENING_DELIVERY                  = 'allowEveningDelivery';
    public const  ALLOW_MONDAY_DELIVERY                   = 'allowMondayDelivery';
    public const  ALLOW_MORNING_DELIVERY                  = 'allowMorningDelivery';
    /**
     * @deprecated now dynamically derived from OnlyRecipientDefinition::getAllowSettingsKey()
     */
    public const  ALLOW_ONLY_RECIPIENT                    = 'allowOnlyRecipient';
    public const  ALLOW_EXPRESS_DELIVERY                  = 'allowExpressDelivery';

    /**
     * @deprecated now dynamically derived from PriorityDeliveryDefinition::getCarrierSettingsKey()
     */
    public const  ALLOW_PRIORITY_DELIVERY                 = 'allowPriorityDelivery';

    /**
     * @deprecated use ALLOW_PICKUP_DELIVERY instead
     */
    public const  ALLOW_PICKUP_LOCATIONS                  = 'allowPickupLocations';
    public const  ALLOW_PICKUP_DELIVERY                   = 'allowPickupLocations';

    /**
     * @deprecated now dynamically derived from SameDayDeliveryDefinition::getAllowSettingsKey()
     */
    public const  ALLOW_SAME_DAY_DELIVERY                 = 'allowSameDayDelivery';

    /**
     * @deprecated now dynamically derived from SaturdayDeliveryDefinition::getAllowSettingsKey()
     */
    public const  ALLOW_SATURDAY_DELIVERY                 = 'allowSaturdayDelivery';

    /**
     * @deprecated now dynamically derived from SignatureDefinition::getAllowSettingsKey()
     */
    public const  ALLOW_SIGNATURE                         = 'allowSignature';

    /**
     * @deprecated use ALLOW_EXPRESS_DELIVERY instead
     */
    public const  ALLOW_DELIVERY_TYPE_EXPRESS             = 'allowDeliveryTypeExpress';
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
    /**
     * @deprecated now dynamically derived from AgeCheckDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_AGE_CHECK                        = 'exportAgeCheck';

    /**
     * @deprecated now dynamically derived from HideSenderDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_HIDE_SENDER                      = 'exportHideSender';

    /**
     * @deprecated now dynamically derived from InsuranceDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_INSURANCE                        = 'exportInsurance';
    public const  EXPORT_INSURANCE_FROM_AMOUNT            = 'exportInsuranceFromAmount';
    public const  EXPORT_INSURANCE_PRICE_PERCENTAGE       = 'exportInsurancePricePercentage';
    public const  EXPORT_INSURANCE_UP_TO                  = 'exportInsuranceUpTo';
    public const  EXPORT_INSURANCE_UP_TO_EU               = 'exportInsuranceUpToEu';
    public const  EXPORT_INSURANCE_UP_TO_ROW              = 'exportInsuranceUpToRow';
    public const  EXPORT_INSURANCE_UP_TO_UNIQUE           = 'exportInsuranceUpToUnique';

    /**
     * @deprecated now dynamically derived from LargeFormatDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_LARGE_FORMAT                     = 'exportLargeFormat';

    /**
     * @deprecated now dynamically derived from OnlyRecipientDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_ONLY_RECIPIENT                   = 'exportOnlyRecipient';

    /**
     * @deprecated now dynamically derived from ReceiptCodeDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_RECEIPT_CODE                     = 'exportReceiptCode';

    /**
     * @deprecated now dynamically derived from DirectReturnDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_RETURN                           = 'exportReturn';
    public const  EXPORT_RETURN_LARGE_FORMAT              = 'exportReturnLargeFormat';
    public const  EXPORT_RETURN_PACKAGE_TYPE              = 'exportReturnPackageType';

    /**
     * @deprecated now dynamically derived from SignatureDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_SIGNATURE                        = 'exportSignature';

    /**
     * @deprecated now dynamically derived from TrackedDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_TRACKED                          = 'exportTracked';

    /**
     * @deprecated now dynamically derived from CollectDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_COLLECT                          = 'exportCollect';

    /**
     * @deprecated now dynamically derived from FreshFoodDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_FRESH_FOOD                       = 'exportFreshFood';

    /**
     * @deprecated now dynamically derived from FrozenDefinition::getCarrierSettingsKey()
     */
    public const  EXPORT_FROZEN                           = 'exportFrozen';
    public const  PRICE_DELIVERY_TYPE_EVENING_DELIVERY    = 'priceDeliveryTypeEvening';
    public const  PRICE_DELIVERY_TYPE_MONDAY_DELIVERY     = 'priceDeliveryTypeMonday';
    public const  PRICE_DELIVERY_TYPE_MORNING_DELIVERY    = 'priceDeliveryTypeMorning';
    public const  PRICE_DELIVERY_TYPE_PICKUP              = 'priceDeliveryTypePickup';
    public const  PRICE_DELIVERY_TYPE_SAME_DAY_DELIVERY   = 'priceDeliveryTypeSameDay';
    public const  PRICE_DELIVERY_TYPE_SATURDAY_DELIVERY   = 'priceDeliveryTypeSaturday';
    public const  PRICE_DELIVERY_TYPE_STANDARD_DELIVERY   = 'priceDeliveryTypeStandard';
    public const  PRICE_DELIVERY_TYPE_EXPRESS_DELIVERY    = 'priceDeliveryTypeExpress';
    /**
     * @deprecated now dynamically derived from OnlyRecipientDefinition::getPriceSettingsKey()
     */
    public const  PRICE_ONLY_RECIPIENT                    = 'priceOnlyRecipient';
    public const  PRICE_PACKAGE_TYPE_DIGITAL_STAMP        = 'pricePackageTypeDigitalStamp';
    public const  PRICE_PACKAGE_TYPE_MAILBOX              = 'pricePackageTypeMailbox';
    public const  PRICE_PACKAGE_TYPE_PACKAGE_SMALL        = 'pricePackageTypePackageSmall';

    /**
     * @deprecated now dynamically derived from SignatureDefinition::getPriceSettingsKey()
     */
    public const  PRICE_SIGNATURE                         = 'priceSignature';

    /**
     * @deprecated now dynamically derived from PriorityDeliveryDefinition::getPriceSettingsKey()
     */
    public const  PRICE_PRIORITY_DELIVERY                 = 'pricePriorityDelivery';
    public const  ALLOW_INTERNATIONAL_MAILBOX             = 'allowInternationalMailbox';
    public const  PRICE_INTERNATIONAL_MAILBOX             = 'priceInternationalMailbox';

    /**
     * @deprecated now dynamically derived from CollectDefinition::getPriceSettingsKey()
     */
    public const  PRICE_COLLECT                           = 'priceCollect';


    protected $attributes = [
        'id'               => self::ID,
        self::CARRIER_NAME => null,

        self::ALLOW_DELIVERY_OPTIONS                  => false,
        self::ALLOW_STANDARD_DELIVERY                 => false,
        self::ALLOW_EVENING_DELIVERY                  => false,
        self::ALLOW_MONDAY_DELIVERY                   => false,
        self::ALLOW_MORNING_DELIVERY                  => false,
        self::ALLOW_EXPRESS_DELIVERY                  => false,
        self::ALLOW_PICKUP_DELIVERY                   => false,
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
        self::PRICE_DELIVERY_TYPE_EVENING_DELIVERY    => 0,
        self::PRICE_DELIVERY_TYPE_MONDAY_DELIVERY     => 0,
        self::PRICE_DELIVERY_TYPE_MORNING_DELIVERY    => 0,
        self::PRICE_DELIVERY_TYPE_PICKUP              => 0,
        self::PRICE_DELIVERY_TYPE_SAME_DAY_DELIVERY   => 0,
        self::PRICE_DELIVERY_TYPE_SATURDAY_DELIVERY   => 0,
        self::PRICE_DELIVERY_TYPE_STANDARD_DELIVERY   => 0,
        self::PRICE_PACKAGE_TYPE_DIGITAL_STAMP        => 0,
        self::PRICE_PACKAGE_TYPE_MAILBOX              => 0,
        self::PRICE_PACKAGE_TYPE_PACKAGE_SMALL        => 0,
        self::ALLOW_INTERNATIONAL_MAILBOX             => false,
        self::PRICE_INTERNATIONAL_MAILBOX             => 0,
        self::PRICE_DELIVERY_TYPE_EXPRESS_DELIVERY    => 0,
    ];

    protected $casts      = [
        self::CARRIER_NAME => 'string',

        self::ALLOW_DELIVERY_OPTIONS                  => 'bool',
        self::ALLOW_STANDARD_DELIVERY                 => 'bool',
        self::ALLOW_EVENING_DELIVERY                  => 'bool',
        self::ALLOW_MONDAY_DELIVERY                   => 'bool',
        self::ALLOW_MORNING_DELIVERY                  => 'bool',
        self::ALLOW_DELIVERY_TYPE_EXPRESS             => 'bool',
        self::ALLOW_PICKUP_DELIVERY                   => 'bool',
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
        self::PRICE_DELIVERY_TYPE_EVENING_DELIVERY    => 'float',
        self::PRICE_DELIVERY_TYPE_MONDAY_DELIVERY     => 'float',
        self::PRICE_DELIVERY_TYPE_MORNING_DELIVERY    => 'float',
        self::PRICE_DELIVERY_TYPE_PICKUP              => 'float',
        self::PRICE_DELIVERY_TYPE_SAME_DAY_DELIVERY   => 'float',
        self::PRICE_DELIVERY_TYPE_SATURDAY_DELIVERY   => 'float',
        self::PRICE_DELIVERY_TYPE_STANDARD_DELIVERY   => 'float',
        self::PRICE_PACKAGE_TYPE_DIGITAL_STAMP        => 'float',
        self::PRICE_PACKAGE_TYPE_MAILBOX              => 'float',
        self::PRICE_PACKAGE_TYPE_PACKAGE_SMALL        => 'float',
        self::ALLOW_INTERNATIONAL_MAILBOX             => 'bool',
        self::PRICE_INTERNATIONAL_MAILBOX             => 'float',
        self::PRICE_DELIVERY_TYPE_EXPRESS_DELIVERY    => 'float',
    ];

    /**
     * Populate attributes and casts dynamically from registered option definitions.
     * Dynamic entries are added first so static definitions win on collision via array_merge.
     */
    protected function initializeResolvesOptionAttributes(): void
    {
        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        $dynamicAttributes = [];
        $dynamicCasts      = [];

        foreach ($definitions as $definition) {
            $exportKey = $definition->getCarrierSettingsKey();

            if ($exportKey !== null) {
                $dynamicAttributes[$exportKey] = TriStateService::INHERIT;
                $dynamicCasts[$exportKey]      = $definition->getShipmentOptionsCast();
            }

            $allowKey = $definition->getAllowSettingsKey();

            if ($allowKey !== null) {
                $dynamicAttributes[$allowKey] = false;
                $dynamicCasts[$allowKey]      = 'bool';
            }

            $priceKey = $definition->getPriceSettingsKey();

            if ($priceKey !== null) {
                $dynamicAttributes[$priceKey] = 0;
                $dynamicCasts[$priceKey]      = 'float';
            }
        }

        $this->attributes = array_merge($dynamicAttributes, $this->attributes);
        $this->casts      = array_merge($dynamicCasts, $this->casts);
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

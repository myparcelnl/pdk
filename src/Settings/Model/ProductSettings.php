<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Base\Concern\ResolvesOptionAttributes;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * @property int<-1>|string $countryOfOrigin
 * @property int<-1>|string $customsCode
 * @property int<-1|0|1>    $disableDeliveryOptions
 * @property int            $dropOffDelay
 * @property int<-1|0|1>    $exportAgeCheck
 * @property int<-1|0|1>    $exportHideSender
 * @property int<-1|0|1>    $exportInsurance
 * @property int<-1|0|1>    $exportLargeFormat
 * @property int<-1|0|1>    $exportOnlyRecipient
 * @property int<-1|0|1>    $exportReturn
 * @property int<-1|0|1>    $exportSignature
 * @property int<-1|0|1>    $exportTracked
 * @property int            $fitInDigitalStamp
 * @property int            $fitInMailbox
 * @property int<-1>|string $packageType
 * @property int<-1|0|1>    $excludeParcelLockers
 */
class ProductSettings extends AbstractSettingsModel
{
    use ResolvesOptionAttributes;

    public const ID                       = 'product';
    public const COUNTRY_OF_ORIGIN        = 'countryOfOrigin';
    public const COUNTRY_OF_ORIGIN_NONE   = 'none';
    public const CUSTOMS_CODE             = 'customsCode';
    public const DISABLE_DELIVERY_OPTIONS = 'disableDeliveryOptions';
    public const DROP_OFF_DELAY           = 'dropOffDelay';

    /**
     * @deprecated now dynamically derived from AgeCheckDefinition::getProductSettingsKey()
     */
    public const EXPORT_AGE_CHECK = 'exportAgeCheck';

    /**
     * @deprecated now dynamically derived from HideSenderDefinition::getProductSettingsKey()
     */
    public const EXPORT_HIDE_SENDER = 'exportHideSender';

    /**
     * @deprecated now dynamically derived from InsuranceDefinition::getProductSettingsKey()
     */
    public const EXPORT_INSURANCE = 'exportInsurance';

    /**
     * @deprecated now dynamically derived from LargeFormatDefinition::getProductSettingsKey()
     */
    public const EXPORT_LARGE_FORMAT = 'exportLargeFormat';

    /**
     * @deprecated now dynamically derived from OnlyRecipientDefinition::getProductSettingsKey()
     */
    public const EXPORT_ONLY_RECIPIENT = 'exportOnlyRecipient';

    /**
     * @deprecated now dynamically derived from DirectReturnDefinition::getProductSettingsKey()
     */
    public const EXPORT_RETURN = 'exportReturn';

    /**
     * @deprecated now dynamically derived from SignatureDefinition::getProductSettingsKey()
     */
    public const EXPORT_SIGNATURE = 'exportSignature';

    /**
     * @deprecated now dynamically derived from TrackedDefinition::getProductSettingsKey()
     */
    public const EXPORT_TRACKED = 'exportTracked';

    /**
     * @deprecated now dynamically derived from FreshFoodDefinition::getProductSettingsKey()
     */
    public const EXPORT_FRESH_FOOD = 'exportFreshFood';

    /**
     * @deprecated now dynamically derived from FrozenDefinition::getProductSettingsKey()
     */
    public const EXPORT_FROZEN = 'exportFrozen';

    public const FIT_IN_DIGITAL_STAMP   = 'fitInDigitalStamp';
    public const FIT_IN_MAILBOX         = 'fitInMailbox';
    public const PACKAGE_TYPE           = 'packageType';
    public const EXCLUDE_PARCEL_LOCKERS = 'excludeParcelLockers';

    protected $attributes = [
        'id' => self::ID,

        self::COUNTRY_OF_ORIGIN        => TriStateService::INHERIT,
        self::CUSTOMS_CODE             => TriStateService::INHERIT,
        self::DISABLE_DELIVERY_OPTIONS => TriStateService::INHERIT,
        self::DROP_OFF_DELAY           => TriStateService::INHERIT,
        self::FIT_IN_DIGITAL_STAMP     => TriStateService::INHERIT,
        self::FIT_IN_MAILBOX           => TriStateService::INHERIT,
        self::PACKAGE_TYPE             => TriStateService::INHERIT,
        self::EXCLUDE_PARCEL_LOCKERS   => TriStateService::INHERIT,
    ];

    protected $casts = [
        self::COUNTRY_OF_ORIGIN        => TriStateService::TYPE_COERCED,
        self::CUSTOMS_CODE             => TriStateService::TYPE_COERCED,
        self::DISABLE_DELIVERY_OPTIONS => TriStateService::TYPE_STRICT,
        self::DROP_OFF_DELAY           => 'int',
        self::FIT_IN_DIGITAL_STAMP     => 'int',
        self::FIT_IN_MAILBOX           => 'int',
        self::PACKAGE_TYPE             => TriStateService::TYPE_COERCED,
        self::EXCLUDE_PARCEL_LOCKERS   => TriStateService::TYPE_STRICT,
    ];

    /**
     * Populate attributes and casts dynamically from registered option definitions.
     * Dynamic entries are added first so static definitions win on collision via array_merge.
     */
    protected function initializeResolvesOptionAttributes(): void
    {
        [$dynamicAttributes, $dynamicCasts] = $this->resolveOptionAttributes(
            static function (OrderOptionDefinitionInterface $definition): ?string {
                return $definition->getProductSettingsKey();
            },
            TriStateService::INHERIT,
            static function (OrderOptionDefinitionInterface $definition): string {
                return $definition->getShipmentOptionsCast();
            }
        );

        $this->attributes = array_merge($dynamicAttributes, $this->attributes);
        $this->casts      = array_merge($dynamicCasts, $this->casts);
    }
}

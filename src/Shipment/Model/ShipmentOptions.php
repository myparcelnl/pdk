<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Base\Concern\ResolvesOptionAttributes;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Support\Arr;

/**
 * This model represents the shipment options as they exist within the DeliveryOptions of a Shipment.
 * They mostly correspond to the shipment options as they exist within the MyParcel API delivery options endpoint, not the shipment options are supported by capabilities and POST /shipments endpoint.
 *
 * @TODO: This should be based off of dynamic shipment option names, but currently from the openApi spec there are no enum equivalents, only Models with (runtime) attribute constraints.
 *
 * @property int<-1>|string|null $labelDescription
 * @property int                 $insurance
 * @property int<-1|0|1>         $ageCheck
 * @property int<-1|0|1>         $hideSender
 * @property int<-1|0|1>         $largeFormat
 * @property int<-1|0|1>         $onlyRecipient
 * @property int<-1|0|1>         $priorityDelivery
 * @property int<-1|0|1>         $receiptCode
 * @property int<-1|0|1>         $return
 * @property int<-1|0|1>         $sameDayDelivery
 * @property int<-1|0|1>         $signature
 * @property int<-1|0|1>         $tracked
 * @property int<-1|0|1>         $collect
 * @property int<-1|0|1>         $excludeParcelLockers
 * @property int<-1|0|1>         $freshFood
 * @property int<-1|0|1>         $frozen
 * @property int<-1|0|1>         $saturdayDelivery
 */
class ShipmentOptions extends Model
{
    use ResolvesOptionAttributes;

    public const LABEL_DESCRIPTION = 'labelDescription';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const INSURANCE         = 'insurance';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const AGE_CHECK         = 'ageCheck';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const DIRECT_RETURN     = 'return';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const HIDE_SENDER       = 'hideSender';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const LARGE_FORMAT      = 'largeFormat';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const ONLY_RECIPIENT    = 'onlyRecipient';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const PRIORITY_DELIVERY = 'priorityDelivery';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const RECEIPT_CODE      = 'receiptCode';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const SAME_DAY_DELIVERY = 'sameDayDelivery';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const SATURDAY_DELIVERY = 'saturdayDelivery';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const MONDAY_DELIVERY   = 'mondayDelivery';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const SIGNATURE         = 'signature';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const TRACKED           = 'tracked';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const COLLECT           = 'collect';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const EXCLUDE_PARCEL_LOCKERS = 'excludeParcelLockers';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const FRESH_FOOD        = 'freshFood';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const FROZEN            = 'frozen';

    /**
     * @deprecated Use definition's getShipmentOptionsKey() instead
     */
    public const COOLED_DELIVERY   = 'cooledDelivery';

    /**
     * @deprecated Use option definitions to determine available shipment options dynamically instead
     */
    public const ALL_SHIPMENT_OPTIONS = [
        self::LABEL_DESCRIPTION,
        self::INSURANCE,
        self::AGE_CHECK,
        self::DIRECT_RETURN,
        self::HIDE_SENDER,
        self::LARGE_FORMAT,
        self::ONLY_RECIPIENT,
        self::PRIORITY_DELIVERY,
        self::RECEIPT_CODE,
        self::SAME_DAY_DELIVERY,
        self::SIGNATURE,
        self::TRACKED,
        self::COLLECT,
        self::FRESH_FOOD,
        self::FROZEN,
        self::SATURDAY_DELIVERY,
        self::MONDAY_DELIVERY,
    ];

    protected $attributes = [
        self::LABEL_DESCRIPTION => null,
    ];

    protected $casts = [
        self::LABEL_DESCRIPTION => TriStateService::TYPE_STRING,
    ];

    /**
     * Get all shipment option keys from registered definitions.
     *
     * @return string[]
     */
    public static function getAllShipmentOptionKeys(): array
    {
        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        return array_values(array_filter(array_map(
            static function (OrderOptionDefinitionInterface $definition): ?string {
                return $definition->getShipmentOptionsKey();
            },
            $definitions
        )));
    }

    /**
     * Populate attributes and casts dynamically from registered option definitions.
     * Each definition declares its own cast type and default value.
     * Dynamic entries are added first so static definitions win on collision via array_merge.
     */
    protected function initializeResolvesOptionAttributes(): void
    {
        [$optionAttributes, $optionCasts] = $this->resolveOptionAttributes(
            static function (OrderOptionDefinitionInterface $definition) {
                return $definition->getShipmentOptionsKey();
            },
            TriStateService::INHERIT,
            static function (OrderOptionDefinitionInterface $definition): string {
                return $definition->getShipmentOptionsCast();
            }
        );

        $this->attributes = array_merge($optionAttributes, $this->attributes);
        $this->casts      = array_merge($optionCasts, $this->casts);
    }

    /**
     * Instantiate a ShipmentOptions model based on their OrderOptionDefinition capabilities definition.
     *
     * @param array $data
     * @return ShipmentOptions
     */
    public static function fromCapabilitiesDefinitions(array $data): self
    {
        // Abuse the OrderOptionsDefinition as they contain the mapping between capabilities and shipment option keys

        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        // Given $data is an array of shipment option in the format ['name' => -1/0/1], find a definition matching the data
        foreach ($data as $shipmentOptionName => $value) {
            /**
             * @var OrderOptionDefinitionInterface|null $definition
             */
            $definition = Arr::first($definitions, static function (OrderOptionDefinitionInterface $definition) use ($shipmentOptionName) {
                return $definition->getCapabilitiesOptionsKey() === $shipmentOptionName;
            });

            if (!$definition) {
                continue;
            }

            // Map the shipment option name to the corresponding shipment option key and retain the value
            $data[$definition->getShipmentOptionsKey()] = $value;

            // Unset the original shipment option name as it's not used in the ShipmentOptions model
            unset($data[$shipmentOptionName]);
        }

        return new self($data);
    }

    /**
     * Given an existing ShipmentOptions model, returns an array of that model with the V2 definition keys
     * @param ShipmentOptions $shipmentOptions
     * @return array
     */
    public static function toCapabilitiesDefinitions(ShipmentOptions $shipmentOptions): array
    {
        $data = [];

        /** @var OrderOptionDefinitionInterface[] $definitions */
        $definitions = Pdk::get('orderOptionDefinitions');

        foreach ($definitions as $definition) {
            $shipmentOptionsKey = $definition->getShipmentOptionsKey();
            $capabilitiesOptionsKey = $definition->getCapabilitiesOptionsKey();

            if (!$shipmentOptionsKey || !$capabilitiesOptionsKey) {
                continue;
            }

            $data[$capabilitiesOptionsKey] = $shipmentOptions->{$shipmentOptionsKey};
        }

        return $data;
    }
}

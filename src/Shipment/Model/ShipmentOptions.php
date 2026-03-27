<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\Facade\Pdk;
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
    public const LABEL_DESCRIPTION = 'labelDescription';
    public const INSURANCE         = 'insurance';
    public const AGE_CHECK         = 'ageCheck';
    public const DIRECT_RETURN     = 'return';
    public const HIDE_SENDER       = 'hideSender';
    public const LARGE_FORMAT      = 'largeFormat';
    public const ONLY_RECIPIENT    = 'onlyRecipient';
    public const PRIORITY_DELIVERY = 'priorityDelivery';
    public const RECEIPT_CODE      = 'receiptCode';
    public const SAME_DAY_DELIVERY = 'sameDayDelivery';

    public const SATURDAY_DELIVERY = 'saturdayDelivery';

    public const MONDAY_DELIVERY   = 'mondayDelivery';

    public const SIGNATURE         = 'signature';
    public const TRACKED           = 'tracked';
    public const COLLECT           = 'collect';
    public const EXCLUDE_PARCEL_LOCKERS = 'excludeParcelLockers';
    public const FRESH_FOOD        = 'freshFood';
    public const FROZEN            = 'frozen';

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
        self::INSURANCE         => TriStateService::INHERIT,
        self::AGE_CHECK         => TriStateService::INHERIT,
        self::DIRECT_RETURN     => TriStateService::INHERIT,
        self::HIDE_SENDER       => TriStateService::INHERIT,
        self::LARGE_FORMAT      => TriStateService::INHERIT,
        self::ONLY_RECIPIENT    => TriStateService::INHERIT,
        self::PRIORITY_DELIVERY => TriStateService::INHERIT,
        self::RECEIPT_CODE      => TriStateService::INHERIT,
        self::SAME_DAY_DELIVERY => TriStateService::INHERIT,
        self::SIGNATURE         => TriStateService::INHERIT,
        self::TRACKED           => TriStateService::INHERIT,
        self::COLLECT           => TriStateService::INHERIT,
        self::EXCLUDE_PARCEL_LOCKERS => TriStateService::INHERIT,
        self::FRESH_FOOD        => TriStateService::INHERIT,
        self::FROZEN            => TriStateService::INHERIT,
        self::SATURDAY_DELIVERY => TriStateService::INHERIT,
    ];

    protected $casts      = [
        self::LABEL_DESCRIPTION => TriStateService::TYPE_STRING,
        self::INSURANCE         => 'int',
        self::AGE_CHECK         => TriStateService::TYPE_STRICT,
        self::DIRECT_RETURN     => TriStateService::TYPE_STRICT,
        self::HIDE_SENDER       => TriStateService::TYPE_STRICT,
        self::LARGE_FORMAT      => TriStateService::TYPE_STRICT,
        self::ONLY_RECIPIENT    => TriStateService::TYPE_STRICT,
        self::PRIORITY_DELIVERY => TriStateService::TYPE_STRICT,
        self::RECEIPT_CODE      => TriStateService::TYPE_STRICT,
        self::SAME_DAY_DELIVERY => TriStateService::TYPE_STRICT,
        self::SIGNATURE         => TriStateService::TYPE_STRICT,
        self::TRACKED           => TriStateService::TYPE_STRICT,
        self::COLLECT           => TriStateService::TYPE_STRICT,
        self::EXCLUDE_PARCEL_LOCKERS => TriStateService::TYPE_STRICT,
        self::FRESH_FOOD        => TriStateService::TYPE_STRICT,
        self::FROZEN            => TriStateService::TYPE_STRICT,
        self::SATURDAY_DELIVERY => TriStateService::TYPE_STRICT,
    ];

    /**
     * Intantiate a ShipmentOptions model based on their OrderOptionDefinition capabilities definition.
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

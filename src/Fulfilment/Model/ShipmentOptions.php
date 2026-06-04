<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Concern\ResolvesOptionAttributes;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * @property bool   $ageCheck
 * @property bool   $collect
 * @property bool   $cooledDelivery
 * @property string $deliveryDate
 * @property int    $deliveryType
 * @property bool   $dropOffAtPostalPoint
 * @property bool   $hideSender
 * @property int    $insurance
 * @property string $labelDescription
 * @property bool   $largeFormat
 * @property bool   $onlyRecipient
 * @property bool   $priorityDelivery
 * @property int    $packageType
 * @property bool   $receiptCode
 * @property bool   $return
 * @property bool   $sameDayDelivery
 * @property bool   $saturdayDelivery
 * @property bool   $signature
 */
class ShipmentOptions extends Model
{
    use ResolvesOptionAttributes;

    // Non-option attributes remain static
    public $attributes = [
        'deliveryDate'     => null,
        'deliveryType'     => null,
        'labelDescription' => '',
        'packageType'      => null,
    ];

    public $casts = [
        'deliveryDate'     => 'string',
        'deliveryType'     => 'int',
        'labelDescription' => 'string',
        'packageType'      => 'int',
    ];

    /**
     * Populate option attributes dynamically from definitions.
     * Fulfilment uses null as default and converts tri-state casts to bool.
     */
    protected function initializeResolvesOptionAttributes(): void
    {
        [$optionAttributes, $optionCasts] = $this->resolveOptionAttributes(
            static function (OrderOptionDefinitionInterface $definition): ?string {
                return $definition->getShipmentOptionsKey();
            },
            null,
            static function (OrderOptionDefinitionInterface $definition): string {
                $cast = $definition->getShipmentOptionsCast();

                // Fulfilment uses simple booleans instead of tri-state
                return $cast === TriStateService::TYPE_STRICT ? 'bool' : $cast;
            }
        );

        $this->attributes = array_merge($optionAttributes, $this->attributes);
        $this->casts      = array_merge($optionCasts, $this->casts);
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $pdkDeliveryOptions
     *
     * @return static
     */
    public static function fromPdkDeliveryOptions(?DeliveryOptions $pdkDeliveryOptions): self
    {
        if (! $pdkDeliveryOptions) {
            return new static();
        }

        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $orderOptionsService */
        $orderOptionsService = Pdk::get(PdkOrderOptionsServiceInterface::class);

        $calculated = $orderOptionsService->calculateShipmentOptions(
            new PdkOrder(['deliveryOptions' => $pdkDeliveryOptions]),
            PdkOrderOptionsServiceInterface::EXCLUDE_PRODUCT_SETTINGS | PdkOrderOptionsServiceInterface::EXCLUDE_CARRIER_SETTINGS
        );

        return new static(
            array_replace($calculated->deliveryOptions->shipmentOptions->getAttributes(), [
                'packageType'  => $pdkDeliveryOptions->getPackageTypeId(),
                'deliveryType' => $pdkDeliveryOptions->getDeliveryTypeId(),
                'deliveryDate' => $pdkDeliveryOptions->date ? $pdkDeliveryOptions->date
                    ->format(Pdk::get('defaultDateFormat')) : null,
            ])
        );
    }
}

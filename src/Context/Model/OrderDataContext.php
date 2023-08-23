<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Options\Helper\CarrierSettingsDefinitionHelper;
use MyParcelNL\Pdk\App\Options\Helper\ProductSettingsDefinitionHelper;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;

/**
 * @property null|string                                                 $externalIdentifier
 * @property null|string                                                 $apiIdentifier
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration      $customsDeclaration
 * @property \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions              $deliveryOptions
 * @property \MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection $lines
 * @property \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection $notes
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $senderAddress
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $billingAddress
 * @property \MyParcelNL\Pdk\App\Order\Model\ShippingAddress             $shippingAddress
 * @property null|\MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
 * @property null|\DateTimeImmutable                                     $orderDate
 * @property bool                                                        $exported
 * @property int                                                         $shipmentPrice
 * @property int                                                         $shipmentPriceAfterVat
 * @property int                                                         $shipmentVat
 * @property int                                                         $orderPrice
 * @property int                                                         $orderPriceAfterVat
 * @property int                                                         $orderVat
 * @property int                                                         $totalPrice
 * @property int                                                         $totalPriceAfterVat
 * @property int                                                         $totalVat
 * @property \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions              $inheritedDeliveryOptions
 */
class OrderDataContext extends PdkOrder
{
    public const ID = Context::ID_ORDER_DATA;

    public function __construct(?array $data = null)
    {
        $this->attributes['inheritedDeliveryOptions'] = null;
        $this->casts['inheritedDeliveryOptions']      = DeliveryOptions::class;

        parent::__construct($data);
    }

    /**
     * Remove deleted shipments from the array.
     *
     * @param  null|int $flags
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toArray(?int $flags = null): array
    {
        if ($this->cloned) {
            return parent::toArray($flags);
        }

        $clone = clone $this;

        $clone->shipments = $clone->shipments
            ->filterNotDeleted()
            ->values();

        return $clone->toArray($flags);
    }

    /**
     * @return \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions
     * @noinspection PhpUnused
     */
    protected function getInheritedDeliveryOptionsAttribute(): DeliveryOptions
    {
        /** @var TriStateServiceInterface $triStateService */
        $triStateService = Pdk::get(TriStateServiceInterface::class);

        $deliveryOptions = new DeliveryOptions([
            'carrier' => [
                'name' => $this->deliveryOptions->carrier->name,
                'id'   => $this->deliveryOptions->carrier->id,
            ],
        ]);

        $productHelper = new ProductSettingsDefinitionHelper($this);
        $carrierHelper = new CarrierSettingsDefinitionHelper($this);

        /** @var \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $definition */
        foreach (Pdk::get('orderOptionDefinitions') as $definition) {
            $inheritedValue = $triStateService->resolve(
                $productHelper->get($definition),
                $carrierHelper->get($definition)
            );

            $deliveryOptions->shipmentOptions->setAttribute($definition->getShipmentOptionsKey(), $inheritedValue);
        }

        return $deliveryOptions;
    }
}

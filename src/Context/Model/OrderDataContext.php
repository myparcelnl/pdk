<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

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
 * @property Collection<DeliveryOptions>                                 $inheritedDeliveryOptions
 */
class OrderDataContext extends PdkOrder
{
    final public const ID = Context::ID_ORDER_DATA;

    public function __construct(?array $data = null)
    {
        $this->attributes['inheritedDeliveryOptions'] = null;

        parent::__construct($data);
    }

    /**
     * Remove deleted shipments from the array.
     *
     * @param  null|int $flags
     *
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
     * Get the inherited delivery options from product and carrier settings for all available carriers.
     *
     * @return Collection<DeliveryOptions>
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @noinspection PhpUnused
     */
    protected function getInheritedDeliveryOptionsAttribute(): Collection
    {
        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
        $service = Pdk::get(PdkOrderOptionsServiceInterface::class);

        $carriers = AccountSettings::getCarriers();

        return (new Collection($carriers))->mapWithKeys(function (Carrier $carrier) use ($service): array {
            $clonedOrder = new PdkOrder($this->only(['deliveryOptions', 'lines']));
            $newCarrier  = new Carrier($carrier->except(['capabilities', 'returnCapabilities']));

            $clonedOrder->deliveryOptions->carrier = $newCarrier;

            $clonedOrder = $service->calculateShipmentOptions(
                $clonedOrder,
                PdkOrderOptionsServiceInterface::EXCLUDE_SHIPMENT_OPTIONS
            );

            $clonedOrder->deliveryOptions->offsetUnset('carrier');

            return [$carrier->externalIdentifier => $clonedOrder->deliveryOptions->toArrayWithoutNull()];
        });
    }
}

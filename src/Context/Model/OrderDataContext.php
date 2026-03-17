<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\FrontendData;
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
 * @property \MyParcelNL\Pdk\App\Order\Model\PdkPhysicalProperties       $physicalProperties
 * @property null|\DateTimeImmutable                                     $orderDate
 * @property bool                                                        $exported
 * @property bool                                                        $autoExported
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
 * @property array                                                       $digitalStampRanges
 */
class OrderDataContext extends PdkOrder
{
    public const ID = Context::ID_ORDER_DATA;

    protected CarrierRepositoryInterface $carrierRepository;

    public function __construct(?array $data = null)
    {
        $this->attributes['inheritedDeliveryOptions'] = null;
        $this->attributes['digitalStampRanges']       = Pdk::get('digitalStampRanges');
        $this->carrierRepository                      = Pdk::get(CarrierRepositoryInterface::class);

        parent::__construct($data);
    }

    /**
     * Get the order data as an array.
     *
     * @param  null|int $flags
     *
     * @return array
     */
    public function toArray(?int $flags = null): array
    {
        if ($this->cloned) {
            return parent::toArray($flags);
        }

        // Remove deleted shipments from the array.
        $clone = clone $this;
        $clone->shipments = $clone->shipments
            ->filterNotDeleted()
            ->values();

        return $clone->toArray($flags);
    }

    protected function getDeliveryOptionsAttribute(array $value): array
    {
        if (!$value || !$value['carrier']) {
            return $value;
        }

        // If carrier is already an array with full data, use it directly
        // Otherwise, it might be a minimal identifier that needs lookup
        if (is_array($value['carrier']) && count($value['carrier']) > 1) {
            $value['carrier'] = (new Carrier($value['carrier']))->toArray();
        } else {
            // Lookup carrier from repository and convert to array

            if (is_array($value['carrier'])) {
                if (isset($value['carrier']['carrier'])) {
                    $carrier = $this->carrierRepository->find($value['carrier']['carrier']);
                } elseif (isset($value['carrier']['name'])) {
                    $carrier = $this->carrierRepository->findByLegacyName($value['carrier']['name']);
                } elseif (isset($value['carrier']['id'])) {
                    $carrier = $this->carrierRepository->findByLegacyId($value['carrier']['id']);
                } else {
                    $carrier = null;
                }
            } elseif (is_string($value['carrier'])) {
                $carrier = $this->carrierRepository->find($value['carrier']);
            } else {
                $carrier = null;
            }

            $value['carrier'] = $carrier ? $carrier->toArray() : $value['carrier'];
        }

        return $value;
    }

    /**
     * Get the inherited delivery options from product and carrier settings for all available carriers.
     *
     * @return Collection<DeliveryOptions>
     * @noinspection PhpUnused
     */
    protected function getInheritedDeliveryOptionsAttribute(): Collection
    {
        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface $service */
        $service = Pdk::get(PdkOrderOptionsServiceInterface::class);

        $carriers = AccountSettings::getCarriers();

        return (new Collection($carriers))->mapWithKeys(function (Carrier $carrier) use ($service): array {
            $clonedOrder = new PdkOrder($this->only(['deliveryOptions', 'lines']));
            $newCarrier  = $this->carrierRepository->find($carrier->carrier);

            $clonedOrder->deliveryOptions->carrier = $newCarrier;

            $calculatedOrder = $service->calculateShipmentOptions(
                $clonedOrder,
                PdkOrderOptionsServiceInterface::EXCLUDE_SHIPMENT_OPTIONS
            );

            $calculatedOrder->deliveryOptions->offsetUnset('carrier');

            return [$carrier->carrier => $calculatedOrder->deliveryOptions->toArrayWithoutNull()];
        });
    }
}

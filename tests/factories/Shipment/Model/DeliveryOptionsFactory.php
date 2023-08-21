<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTimeInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierFactory;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;
use function MyParcelNL\Pdk\Tests\factory;

/**
 * @template T of DeliveryOptions
 * @method DeliveryOptions make()
 * @method $this withCarrier(array|string|Carrier|CarrierFactory $carrier)
 * @method $this withDate(array|string|DateTimeInterface $date)
 * @method $this withDeliveryType(string $deliveryType)
 * @method $this withLabelAmount(int $labelAmount)
 * @method $this withPackageType(string $packageType)
 * @method $this withPickupLocation(array|RetailLocation|RetailLocationFactory $pickupLocation)
 * @method $this withShipmentOptions(array|ShipmentOptions|ShipmentOptionsFactory $shipmentOptions)
 */
final class DeliveryOptionsFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return DeliveryOptions::class;
    }

    public function withAllShipmentOptions(): self
    {
        return $this->withShipmentOptions(factory(ShipmentOptions::class)->withAllOptions());
    }

    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withCarrier(factory(Carrier::class))
            ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME);
    }
}

<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of CarrierCapabilities
 * @method CarrierCapabilities make()
 * @method $this withDeliveryTypes(array $deliveryTypes)
 * @method $this withFeatures(array $features)
 * @method $this withPackageTypes(array $packageTypes)
 * @method $this withShipmentOptions(array $shipmentOptions)
 */
final class CarrierCapabilitiesFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return CarrierCapabilities::class;
    }

    /**
     * @return $this
     */
    public function withAllDeliveryTypes(): self
    {
        return $this->withDeliveryTypes(DeliveryOptions::DELIVERY_TYPES_NAMES);
    }

    /**
     * @return $this
     */
    public function withAllFeatures(): self
    {
        return $this->withFeatures([
            'dropOffAtPostalPoint'   => true,
            'labelDescriptionLength' => 45,
            'multiCollo'             => true,
        ]);
    }

    /**
     * @return $this
     */
    public function withAllOptions(): self
    {
        return $this->withShipmentOptions(
            array_fill_keys([
                ShipmentOptions::AGE_CHECK,
                ShipmentOptions::DIRECT_RETURN,
                ShipmentOptions::HIDE_SENDER,
                ShipmentOptions::INSURANCE,
                ShipmentOptions::LARGE_FORMAT,
                ShipmentOptions::ONLY_RECIPIENT,
                ShipmentOptions::SAME_DAY_DELIVERY,
                ShipmentOptions::SIGNATURE,
            ], true)
        );
    }

    /**
     * @return $this
     */
    public function withAllPackageTypes(): self
    {
        return $this->withPackageTypes(DeliveryOptions::PACKAGE_TYPES_NAMES);
    }

    /**
     * @return $this
     */
    public function withEverything(): self
    {
        return $this
            ->withAllDeliveryTypes()
            ->withAllFeatures()
            ->withAllOptions()
            ->withAllPackageTypes();
    }
}

<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition\Model;

use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of PropositionCarrierFeatures
 * @method PropositionCarrierFeatures make()
 * @method $this withDeliveryTypes(array $deliveryTypes)
 * @method $this       withMetadata(array $metadata)
 * @method $this withPackageTypes(array $packageTypes)
 * @method $this withShipmentOptions(array $shipmentOptions)
 */
final class PropositionCarrierFeaturesFactory extends AbstractModelFactory
{
    public function fromCarrier(string $carrierName): self
    {
        $foundCarrier = Platform::getCarriers()
            ->firstWhere('name', $carrierName);

        if (! $foundCarrier) {
            return $this;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $features = $foundCarrier->outboundFeatures->toArrayWithoutNull();

        return $this->with($features);
    }

    public function getModel(): string
    {
        return PropositionCarrierFeatures::class;
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
    public function withAllMetadata(): self
    {
        return $this->withMetadata([
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
        return $this->withShipmentOptions([
                PropositionCarrierFeatures::SHIPMENT_OPTION_AGE_CHECK_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_DIRECT_RETURN_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_HIDE_SENDER_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_LARGE_FORMAT_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_ONLY_RECIPIENT_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_PRIORITY_DELIVERY_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_SAME_DAY_DELIVERY_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_SIGNATURE_NAME,
                PropositionCarrierFeatures::SHIPMENT_OPTION_INSURANCE_NAME
            ]);
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
            ->withAllMetadata()
            ->withAllOptions()
            ->withAllPackageTypes();
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Contract;

use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

/**
 * Interface for FrontendDataAdapter that converts new proposition configuration
 * data to the old format that JS-PDK and Delivery Options expect.
 */
interface FrontendDataAdapterInterface
{
    /**
     * Get carriers in the old format that JS-PDK and Delivery Options expect.
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function carrierCollectionToLegacyFormat(CarrierCollection $carrierCollection): CarrierCollection;

    public function convertCarrierToLegacyFormat(Carrier $carrier): Carrier;

    public function getLegacyIdentifier(string $externalIdentifier): string;

    /**
     * Get package types in the old format.
     *
     * @return array
     */
    public function getLegacyPackageTypes(): array;

    /**
     * Get delivery types in the old format.
     *
     * @return array
     */
    public function getLegacyDeliveryTypes(): array;

    /**
     * Get shipment options in the old format.
     *
     * @return array
     */
    public function getLegacyShipmentOptions(): array;
}

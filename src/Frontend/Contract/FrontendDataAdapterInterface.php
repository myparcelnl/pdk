<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Contract;

use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;

/**
 * Interface for FrontendDataAdapter that converts new proposition configuration
 * data to the old format that JS-PDK and Delivery Options expect.
 */
interface FrontendDataAdapterInterface
{
    /**
     * Get carriers in the old format that JS-PDK and Delivery Options expect.
     * This converts the new proposition config carriers to the old carrier structure.
     *
     * @return \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection
     */
    public function getLegacyCarriers(): CarrierCollection;

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

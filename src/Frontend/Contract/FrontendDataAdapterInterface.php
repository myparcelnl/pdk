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
    public function getLegacyCarrierIdentifier(string $carrierIdentifier): string;
}

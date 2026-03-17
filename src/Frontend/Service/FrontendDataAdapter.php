<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Service;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Frontend\Contract\FrontendDataAdapterInterface;

/**
 * FrontendDataAdapter converts new proposition configuration data to the old format
 * that JS-PDK and Delivery Options expect. This ensures backwards compatibility
 * while the backend uses the new proposition configuration.
 */
class FrontendDataAdapter implements FrontendDataAdapterInterface
{
    /**
     * Given a v2 carrier name, return the legacy identifier
     */
    public function getLegacyCarrierIdentifier(string $carrierName): string
    {
        return Carrier::CARRIER_NAME_TO_LEGACY_MAP[$carrierName] ?? $carrierName;
    }
}

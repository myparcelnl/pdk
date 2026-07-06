<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentShipmentOptions;
use MyParcelNL\Sdk\Support\Str;

final class CooledDeliveryDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return Str::camel(RefShipmentShipmentOptions::attributeMap()['cooled_delivery']);
    }

    /**
     * Cooled delivery was removed from the capabilities V2 options as of SDK 11.0.0-beta.28;
     * it remains a settable shipment option ({@see RefShipmentShipmentOptions}) but is no
     * longer capability-gated, so it has no capabilities key (like ExcludeParcelLockers).
     */
    public function getCapabilitiesOptionsKey(): ?string
    {
        return null;
    }

    public function getAllowSettingsKey(): ?string
    {
        return null;
    }

    public function getPriceSettingsKey(): ?string
    {
        return null;
    }
}

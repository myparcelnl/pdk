<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentShipmentOptions;
use MyParcelNL\Sdk\Support\Str;

/**
 * Opting out of track & trace.
 *
 * Replaces the `tracked` option, which the API is retiring. Tracking is the default wherever it is
 * available, so this option only ever expresses an explicit opt-out: it is sent to the API when a
 * merchant switched it on, and omitted otherwise.
 *
 * Carrier- and product-level settings keys are deliberately inherited rather than overridden, so the
 * option keeps the carrier default and the per-product override the old `tracked` setting had. There
 * is no allow or price key: not tracking is cheaper, so a surcharge would need a negative price, and
 * the consumer must not be able to switch tracking off in the checkout.
 */
final class NoTrackingDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return Str::camel(RefShipmentShipmentOptions::attributeMap()['no_tracking']);
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['no_tracking'];
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

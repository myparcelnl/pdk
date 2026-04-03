<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentShipmentOptions;
use MyParcelNL\Sdk\Support\Str;

final class TrackedDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return Str::camel(RefShipmentShipmentOptions::attributeMap()['tracked']);
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['tracked'];
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

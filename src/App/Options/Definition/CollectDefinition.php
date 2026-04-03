<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentShipmentOptions;
use MyParcelNL\Sdk\Support\Str;

final class CollectDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return Str::camel(RefShipmentShipmentOptions::attributeMap()['collect']);
    }

    public function getCapabilitiesOptionsKey(): ?string
    {
        return RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2::attributeMap()['scheduled_collection'];
    }

    /**
     * @TODO: Collect has a priceCollect setting but no allowCollect toggle. The price is sent
     *        to the delivery options frontend but the consumer cannot toggle collect on/off.
     *        Consider adding allowCollect or removing priceCollect.
     */
    public function getAllowSettingsKey(): ?string
    {
        return null;
    }

    public function getProductSettingsKey(): ?string
    {
        return null;
    }
}

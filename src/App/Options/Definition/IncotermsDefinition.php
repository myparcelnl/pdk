<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Options\Definition;

use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentShipmentOptions;
use MyParcelNL\Sdk\Support\Str;

/**
 * The Incoterms rule of a shipment, such as DAP or DDP. Incoterms are international trade terms that
 * say who pays the import duties, taxes and customs clearance in the destination country: the sender
 * or the recipient. The value is a rule, not a tri-state.
 */
final class IncotermsDefinition extends AbstractOrderOptionDefinition
{
    public function getShipmentOptionsKey(): ?string
    {
        return Str::camel(RefShipmentShipmentOptions::attributeMap()['incoterms']);
    }

    /**
     * Override needed, because capabilities does not return it as an `option`: the rules are in the
     * top-level `incoterms` property of a capability.
     *
     * @return null|string
     */
    public function getCapabilitiesRootKey(): ?string
    {
        return 'incoterms';
    }

    public function getProductSettingsKey(): ?string
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

    /**
     * Keeps INHERIT for "no rule chosen" and keeps a rule as a string.
     *
     * @return string
     */
    public function getShipmentOptionsCast(): string
    {
        return TriStateService::TYPE_COERCED;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Service;

use MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface;
use MyParcelNL\Pdk\App\Options\Definition\InsuranceDefinition;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Util\InsuranceTierMath;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;

/**
 * Answers questions about a whole {@see Carrier} from data already loaded on the model.
 *
 * Sister service to {@see CapabilitiesValidationService}: anything answerable from the
 * loaded Carrier (packageTypes, options, collo, insurance amounts) belongs here.
 * Anything that needs a fresh /capabilities API call belongs on the sister service.
 *
 * Method names use common verbs (`supports*`, `has*`, `getAllowed*`) without subject
 * prefixes — the class name carries the subject.
 */
class CarrierValidationService
{
    /**
     * Whether the carrier supports the given shipment option.
     *
     * Capabilities advertise options as keys in the carrier's options map; a present
     * key (even with an empty configuration) signals the option is available.
     *
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     * @param  class-string<\MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface>|\MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $definition
     *
     * @return bool
     */
    public function supportsShipmentOption(Carrier $carrier, $definition): bool
    {
        $resolved = $definition instanceof OrderOptionDefinitionInterface
            ? $definition
            : new $definition();

        $capabilitiesKey = $resolved->getCapabilitiesOptionsKey();

        if ($capabilitiesKey === null) {
            return false;
        }

        $options = $carrier->toArray()['options'] ?? [];

        return array_key_exists($capabilitiesKey, $options);
    }

    /**
     * Whether the carrier supports the MAILBOX package type.
     */
    public function supportsMailbox(Carrier $carrier): bool
    {
        return $this->supportsPackageType($carrier, RefShipmentPackageTypeV2::MAILBOX);
    }

    /**
     * Whether the carrier supports multi-collo shipments (a single parent
     * shipment paired with secondary shipments sharing the same label group).
     *
     * Backed by the carrier's `collo.max` capability: anything above 1 collo
     * means multi-collo is available.
     */
    public function supportsMultiCollo(Carrier $carrier): bool
    {
        $collo = $carrier->collo;

        return $collo !== null && (int) $collo->getMax() > 1;
    }

    /**
     * Whether the carrier supports the DIGITAL_STAMP package type.
     */
    public function supportsDigitalStamp(Carrier $carrier): bool
    {
        return $this->supportsPackageType($carrier, RefShipmentPackageTypeV2::DIGITAL_STAMP);
    }

    /**
     * Insurance tier ladder allowed for the carrier (cents).
     *
     * Returns an empty array when the carrier does not support insurance.
     *
     * @return int[]
     */
    public function getAllowedInsuranceAmounts(Carrier $carrier): array
    {
        if (! $this->supportsShipmentOption($carrier, InsuranceDefinition::class)) {
            return [];
        }

        $insured = $carrier->options->getInsurance()->getInsuredAmount();

        return InsuranceTierMath::buildTiers(
            $insured->getMin()->getAmount(),
            $insured->getMax()->getAmount()
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     * @param  string                                $packageType V2 package type identifier
     *
     * @return bool
     */
    private function supportsPackageType(Carrier $carrier, string $packageType): bool
    {
        $packageTypes = $carrier->packageTypes ?? [];

        return in_array($packageType, $packageTypes, true);
    }
}

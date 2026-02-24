<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment;

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesPostCapabilitiesRequestV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesPostContractDefinitionsRequestV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesResponsesCapabilitiesV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesResponsesContractDefinitionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2;

/**
 * Service for retrieving capabilities information from the CoreAPI.
 *
 * This service provides access to two main capabilities endpoints:
 * - Shipment capabilities: Calculate available delivery options, package types, and shipment options
 *   based on specific shipment parameters (sender, recipient, carrier, etc.)
 * - Contract definitions: Retrieve contract-specific configuration and available options for carriers
 *
 * **Usage Examples:**
 * ```php
 * $service = new CapabilitiesService();
 *
 * // Get dynamic capabilities for a specific shipment
 * $capabilities = $service->getCapabilities([
 *     'carrier' => 'postnl',
 *     'recipient' => ['cc' => 'NL', 'postal_code' => '2132WT'],
 *     'package_type' => 'package',
 * ]);
 *
 * // Get contract definitions for a carrier
 * $definitions = $service->getContractDefinitions('postnl');
 * ```
 *
 * @see \MyParcelNL\Pdk\SdkApi\Service\CoreApi\AbstractShipmentApiService
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApi\Api\ShipmentApi::postCapabilities()
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApi\Api\ShipmentApi::postCapabilitiesContractDefinitions()
 */
class CapabilitiesService extends AbstractShipmentApiService
{
    /**
     * Get shipment capabilities based on specific parameters.
     *
     * Calculates and returns available delivery options, shipment options, and capabilities
     * based on shipment details like sender, recipient, carrier, package type, etc.
     * This is useful for building checkout flows with dynamic delivery options.
     *
     * @param array $parameters Array of shipment parameters including:
     *                          - carrier: string (required) - Carrier identifier
     *                          - recipient: array - Recipient address details (cc, postal_code, etc.)
     *                          - sender: array - Sender address details
     *                          - package_type: string - Package type (package, mailbox, letter, etc.)
     *                          - physical_properties: array - Weight, dimensions
     *                          - options: array - Requested shipment options
     *                          - delivery_type: string - Delivery type (standard, morning, evening)
     *                          - direction: string - Direction (outbound, inbound)
     *                          - pickup: array - Pickup location details
     *
     * @return RefCapabilitiesResponseCapabilityV2[] The capabilities response with available options
     * @throws \MyParcelNL\Sdk\Client\Generated\CoreApi\ApiException On API errors
     * @throws \InvalidArgumentException If required parameters are missing
     */
    public function getCapabilities(array $parameters): array
    {
        /** @var CapabilitiesResponsesCapabilitiesV2 $response */
        $response = $this->executeOperationWithErrorHandling(function () use ($parameters): CapabilitiesResponsesCapabilitiesV2 {
            return $this->shipmentApi->postCapabilities(
                $this->getUserAgent(),
                new CapabilitiesPostCapabilitiesRequestV2($parameters)
            );
        }, 'postCapabilities');

        return $response->getResults();
    }

    /**
     * Get contract definitions for carriers.
     *
     * Retrieves contract-specific configuration including available package types, shipment options,
     * and capabilities for specific carriers based on the user's contracts. This provides static
     * configuration data rather than dynamic calculations.
     *
     * @param string|null $carrier The carrier identifier (e.g., 'postnl', 'dpd', 'dhl') to get a
     *                             specific carrier's contract definitions, or null to retrieve all.
     * @todo type the parameter! or document the enum
     *
     * @return RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2[] An array of contract definitions per carrier.
     * @throws \MyParcelNL\Sdk\Client\Generated\CoreApi\ApiException On API errors
     */
    public function getContractDefinitions(?string $carrier): array
    {
        // Set carrier explicitly as it otherwise does not validate via the constructor.
        $request = new CapabilitiesPostContractDefinitionsRequestV2();
        if ($carrier) {
            $request->setCarrier($carrier);
        }

        /**
         * @var CapabilitiesResponsesContractDefinitionsV2 $response
         */
        $response = $this->executeOperationWithErrorHandling(function () use ($request): CapabilitiesResponsesContractDefinitionsV2 {
            return $this->shipmentApi->postCapabilitiesContractDefinitions(
                $this->getUserAgent(),
                $request
            );
        }, 'postContractDefinitions');

        return $response->getItems();
    }
}

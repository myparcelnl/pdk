<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment;

use GuzzleHttp\HandlerStack;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesPostCapabilitiesRequestV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesPostContractDefinitionsRequestV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesResponsesCapabilitiesV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ModelInterface;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2;
use Psr\Http\Message\RequestInterface;

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
 *     'carrier' => 'POSTNL',
 *     'recipient' => ['country_code' => 'NL', 'postal_code' => '2132WT'],
 *     'package_type' => 'PACKAGE',
 * ]);
 *
 * // Get contract definitions for a carrier
 * $definitions = $service->getContractDefinitions('POSTNL');
 * ```
 *
 * @see \MyParcelNL\Pdk\SdkApi\Service\CoreApi\AbstractShipmentApiService
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApi\Api\ShipmentApi::postCapabilities()
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApi\Api\ShipmentApi::postCapabilitiesContractDefinitions()
 */
class CapabilitiesService extends AbstractShipmentApiService
{
    /**
     * Create a HandlerStack with custom middleware for capabilities endpoints.
     *
     * Adds middleware that enforces the Accept header to 'application/json;charset=utf-8;version=2'
     * for all capabilities endpoints, ensuring consistent API version responses.
     *
     * @return HandlerStack The configured handler stack
     */
    protected function createGuzzleClientHandlerStack(): HandlerStack
    {
        $stack = parent::createGuzzleClientHandlerStack();

        // Add middleware to enforce version 2 Accept header for capabilities endpoints
        $stack->push(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $path = $request->getUri()->getPath();

                // Apply to both capabilities endpoints
                if (strpos($path, '/shipments/capabilities') !== false) {
                    $request = $request->withHeader('Accept', 'application/json;charset=utf-8;version=2');
                }

                return $handler($request, $options);
            };
        });

        return $stack;
    }

    /**
     * Get shipment capabilities based on specific parameters.
     *
     * Calculates and returns available delivery options, shipment options, and capabilities
     * based on shipment details like sender, recipient, carrier, package type, etc.
     * This is useful for building checkout flows with dynamic delivery options.
     *
     * Nested arrays are wrapped into the typed SDK models the schema declares,
     * so that attributeMap-driven serialization produces V2-correct wire keys
     * (e.g. {@see CapabilitiesRecipientV2}'s `country_code` property → wire `countryCode`).
     *
     * @param array $parameters Array of shipment parameters. Keys and nested keys must match
     *                          the property names declared in the corresponding V2 model's
     *                          `openAPITypes` (snake_case), not the wire keys:
     *                          - recipient: array (required) - {country_code, postal_code, is_business}
     *                          - carrier: string (optional) - Carrier identifier
     *                          - sender: array (optional) - Sender address details
     *                          - package_type: string (optional) - Package type (PACKAGE, MAILBOX, LETTER, ...)
     *                          - physical_properties: array (optional) - {weight, height, width, length}
     *                          - options: array (optional) - Requested shipment options
     *                          - delivery_type: string (optional) - Delivery type (standard, morning, evening)
     *                          - direction: string (optional) - Direction (outbound, inbound)
     *                          - pickup: array (optional) - Pickup location details
     *
     * @return RefCapabilitiesResponseCapabilityV2[] The capabilities response with available options
     * @throws \MyParcelNL\Sdk\Client\Generated\CoreApi\ApiException On API errors
     * @throws \InvalidArgumentException If required parameters are missing
     */
    public function getCapabilities(array $parameters): array
    {
        /** @var CapabilitiesPostCapabilitiesRequestV2 $request */
        $request = $this->hydrateModel(CapabilitiesPostCapabilitiesRequestV2::class, $parameters);

        /** @var CapabilitiesResponsesCapabilitiesV2 $response */
        $response = $this->shipmentApi->postCapabilities($this->getUserAgent(), $request);

        return $response->getResults();
    }

    /**
     * Recursively wrap nested array data into the typed SDK model the schema expects,
     * so the SDK's attributeMap-driven serialization produces V2-correct wire keys.
     *
     * Walks `openAPITypes` and instantiates any property whose declared type is a
     * `ModelInterface` subclass when the input value is still a raw array. Already-typed
     * model instances are passed through untouched. Primitives, enums, and arrays-of-scalars
     * are left alone.
     *
     * @param  class-string<ModelInterface> $modelClass
     * @param  array                        $data
     *
     * @return ModelInterface
     */
    private function hydrateModel(string $modelClass, array $data): ModelInterface
    {
        foreach ($modelClass::openAPITypes() as $property => $type) {
            if (! is_array($data[$property] ?? null)) {
                continue;
            }

            $nestedClass = ltrim((string) $type, '\\');

            if (class_exists($nestedClass) && is_subclass_of($nestedClass, ModelInterface::class)) {
                $data[$property] = $this->hydrateModel($nestedClass, $data[$property]);
            }
        }

        return new $modelClass($data);
    }

    /**
     * Get contract definitions for carriers.
     *
     * Retrieves contract-specific configuration including available package types, shipment options,
     * and capabilities for specific carriers based on the user's contracts. This provides static
     * configuration data rather than dynamic calculations.
     *
     * @param string|null $carrier The carrier identifier (e.g., 'POSTNL', 'DPD', 'DHL_FOR_YOU') to get a
     *                             specific carrier's contract definitions, or null to retrieve all.
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

        $response = $this->shipmentApi->postCapabilitiesContractDefinitions(
            $this->getUserAgent(),
            $request
        );

        return $response->getItems();
    }
}

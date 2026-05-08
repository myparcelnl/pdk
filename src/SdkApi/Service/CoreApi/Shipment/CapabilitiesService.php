<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment;

use GuzzleHttp\HandlerStack;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesPostCapabilitiesRequestV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesPostContractDefinitionsRequestV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesResponsesCapabilitiesV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ModelInterface;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseOptionsOptionsV2;
use MyParcelNL\Sdk\Support\Str;
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
     * @param array $parameters Array of shipment parameters. Keys may be either the SDK property
     *                          names (snake_case from `openAPITypes`) or the API wire keys
     *                          (camelCase from `attributeMap`). Conversion happens at every
     *                          nesting level. Documented as snake_case for clarity:
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
     * @param bool $filterSupported Whether to filter out carriers, delivery types, package types and options that are not supported by this PDK version.
     * Defaults to return capabilities unfiltered.
     *
     * @return RefCapabilitiesResponseCapabilityV2[] The capabilities response with available options
     * @throws \MyParcelNL\Sdk\Client\Generated\CoreApi\ApiException On API errors
     * @throws \InvalidArgumentException If required parameters are missing
     */
    public function getCapabilities(array $parameters, bool $filterSupported = false): array
    {
        /** @var CapabilitiesPostCapabilitiesRequestV2 $request */
        $request = $this->hydrateModel(CapabilitiesPostCapabilitiesRequestV2::class, $parameters);

        /** @var CapabilitiesResponsesCapabilitiesV2 $response */
        $response = $this->shipmentApi->postCapabilities($this->getUserAgent(), $request);
        $results  = $response->getResults();

        return $filterSupported ? $this->filterSupportedCapabilities($results) : $results;
    }

    /**
     * Drop carriers and capabilities this PDK version does not recognise, and narrow each surviving model's
     * delivery types, package types and options to the PDK-supported subset.
     *
     * Mutates the passed-in SDK models in place. The two response models share an identical
     * carrier/deliveryTypes/packageTypes/options surface area, so a union typehint avoids
     * the generated-model template gymnastics phpstan would otherwise need.
     *
     * @param  array<int, RefCapabilitiesResponseCapabilityV2|RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2> $models
     *
     * @return array<int, RefCapabilitiesResponseCapabilityV2|RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2>
     */
    private function filterSupportedCapabilities(array $models): array
    {
        $supported = [];

        foreach ($models as $model) {
            /** @var string $carrierName */
            $carrierName = $model->getCarrier();
            if (! Carrier::isSupported($carrierName)) {
                continue;
            }

            $model->setDeliveryTypes(array_values(array_filter(
                // @phpstan-ignore nullCoalesce.expr (attribute can be null if model is manually constructed with missing fields, but API responses always include it as an array)
                $model->getDeliveryTypes() ?? [],
                // @phpstan-ignore argument.type (SDK enum values come back as plain strings)
                static function (string $type): bool {
                    return DeliveryOptions::isDeliveryTypeSupported($type);
                }
            )));

            $model->setPackageTypes(array_values(array_filter(
                // @phpstan-ignore nullCoalesce.expr (attribute can be null if model is manually constructed with missing fields, but API responses always include it as an array)
                $model->getPackageTypes() ?? [],
                // @phpstan-ignore argument.type (SDK enum values come back as plain strings)
                static function (string $type): bool {
                    return DeliveryOptions::isPackageTypeSupported($type);
                }
            )));

            $options = $model->getOptions();
            if ($options !== null) {
                $model->setOptions($this->stripUnregisteredOptions($options));
            }

            $supported[] = $model;
        }

        return $supported;
    }

    /**
     * Remove options whose camelCase key has no registered OrderOptionDefinition in this PDK.
     *
     * Uses {@see \ArrayAccess::offsetUnset()} on the SDK options model rather than the typed
     * setters: many setters throw on null for non-nullable fields, but offsetUnset cleanly
     * removes the property from the underlying container so the serializer skips it.
     *
     * @param RefCapabilitiesResponseOptionsOptionsV2|RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2 $options
     * @return RefCapabilitiesResponseOptionsOptionsV2|RefCapabilitiesContractDefinitionsResponseOptionsOptionsV2
     */
    private function stripUnregisteredOptions($options): object
    {
        $allowed      = Carrier::getRegisteredCapabilitiesKeys();

        foreach ((array) $options->jsonSerialize() as $name => $option) {
            if (! isset($allowed[$name])) {
                // Convert name back to snake_case for the offset, as the SDK models store properties in snake_case but expose them as camelCase via attributeMap-driven getters/setters and jsonSerialize.
                $snakeName = Str::snake($name);
                // @phpstan-ignore unset.offset
                unset($options[$snakeName]);
            }
        }

        return $options;
    }

    /**
     * Recursively normalize an input array into a typed SDK model, in a single walk:
     *  - Convert API-style camelCase keys to SDK-input snake_case using the model's `attributeMap`.
     *  - Wrap nested arrays whose declared type is a `ModelInterface` subclass into typed models.
     *
     * Already-snake_case keys and already-typed model instances pass through. Primitives,
     * enums, and arrays-of-scalars are left alone.
     *
     * @param  class-string<ModelInterface> $modelClass
     * @param  array                        $data
     *
     * @return ModelInterface
     */
    private function hydrateModel(string $modelClass, array $data): ModelInterface
    {
        $camelToSnake = array_flip($modelClass::attributeMap());
        $openAPITypes = $modelClass::openAPITypes();
        $normalized   = [];

        foreach ($data as $key => $value) {
            $property    = $camelToSnake[$key] ?? $key;
            $nestedClass = ltrim((string) ($openAPITypes[$property] ?? ''), '\\');

            if (
                is_array($value)
                && class_exists($nestedClass)
                && is_subclass_of($nestedClass, ModelInterface::class)
            ) {
                $value = $this->hydrateModel($nestedClass, $value);
            }

            $normalized[$property] = $value;
        }

        return new $modelClass($normalized);
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
     * @param bool $filterSupported Whether to drop carriers, delivery types, package types and options
     *                              that are not supported by this PDK version. Defaults to false.
     *
     * @return RefCapabilitiesContractDefinitionsResponseContractDefinitionsV2[] An array of contract definitions per carrier.
     * @throws \MyParcelNL\Sdk\Client\Generated\CoreApi\ApiException On API errors
     */
    public function getContractDefinitions(?string $carrier, bool $filterSupported = false): array
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
        $items = $response->getItems();

        return $filterSupported ? $this->filterSupportedCapabilities($items) : $items;
    }
}

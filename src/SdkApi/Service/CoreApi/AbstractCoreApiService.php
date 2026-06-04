<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApi;

use MyParcelNL\Pdk\SdkApi\Service\AbstractSdkApiService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Configuration as CoreApiConfiguration;

/**
 * Abstract base class for CoreAPI-specific services.
 *
 * This class extends the generic SDK service base with CoreAPI-specific configuration,
 * providing a ready-to-use Configuration object for OpenAPI CoreAPI client classes.
 *
 * The CoreAPI namespace contains endpoints for:
 * - Shipments (create, retrieve, update, labels)
 * - Capabilities (contract definitions, delivery options)
 * - Notifications
 * - Track & trace
 *
 * **Usage:**
 * Extend this class to create services for specific business domains within the CoreAPI.
 * Use {@see getApiConfig()} in your constructor when instantiating OpenAPI API classes.
 *
 * **Example:**
 * ```php
 * class ContractDefinitionsService extends AbstractCoreApiService
 * {
 *     protected ShipmentApi $shipmentApi;
 *
 *     public function __construct()
 *     {
 *         $this->shipmentApi = new ShipmentApi(null, $this->getApiConfig());
 *     }
 *
 *     public function getContractDefinitions(string $carrier): array
 *     {
 *         return $this->executeOperationWithErrorHandling(
 *             fn() => $this->shipmentApi->postCapabilitiesContractDefinitions(...),
 *             'postContractDefinitions'
 *         );
 *     }
 * }
 * ```
 *
 * @see \MyParcelNL\Pdk\SdkApi\Service\AbstractSdkApiService
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApi\Configuration
 */
abstract class AbstractCoreApiService extends AbstractSdkApiService
{
    /**
     * Get a configured CoreAPI configuration object.
     *
     * Factory: builds a fresh Configuration instance and delegates field-setting to
     * {@see AbstractSdkApiService::applyConfigSettings()} so that the same business logic
     * is shared with {@see AbstractSdkApiService::refreshApiConfig()}.
     *
     * @return CoreApiConfiguration The configured API configuration
     */
    public function getApiConfig(): CoreApiConfiguration
    {
        return $this->applyConfigSettings(new CoreApiConfiguration());
    }
}

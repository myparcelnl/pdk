<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApi;

use MyParcelNL\Pdk\Base\Config;
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
     * Builds a Configuration instance with:
     * - Access token (base64-encoded API key)
     * - User agent string (platform + PDK + PHP versions)
     * - Host URL (conditional on environment: production or acceptance)
     *
     * @return CoreApiConfiguration The configured API configuration
     */
    public function getApiConfig(): CoreApiConfiguration
    {
        $config = new CoreApiConfiguration();
        $apiKey = $this->getApiKey();
        $config->setAccessToken($apiKey ? base64_encode($apiKey) : '');
        $config->setUserAgent($this->getUserAgent());

        // Set an alternate host for acceptance testing if the environment is set to acceptance,
        // otherwise use the default host from the generated OpenAPI client.
        if ($this->isAcceptanceEnvironment()) {
            $config->setHost(Config::API_URL_ACCEPTANCE);
        }

        return $config;
    }
}

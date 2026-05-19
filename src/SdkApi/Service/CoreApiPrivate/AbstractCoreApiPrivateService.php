<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApiPrivate;

use MyParcelNL\Pdk\SdkApi\Service\AbstractSdkApiService;
use MyParcelNL\Sdk\Client\Generated\CoreApiPrivate\Configuration as CoreApiPrivateConfiguration;

/**
 * Abstract base class for CoreAPI Private-specific services.
 *
 * This class extends the generic SDK service base with CoreAPI Private-specific configuration,
 * providing a ready-to-use Configuration object for OpenAPI CoreAPI Private client classes.
 *
 * The CoreAPI Private namespace contains endpoints for:
 * - Shipping rules implications
 *
 * **Usage:**
 * Extend this class to create services for specific business domains within the CoreAPI Private.
 * Use {@see getApiConfig()} in your constructor when instantiating OpenAPI API classes.
 *
 * **Example:**
 * ```php
 * class ImplicationsService extends AbstractCoreApiPrivateService
 * {
 *     protected ImplicationsApi $implicationsApi;
 *
 *     public function __construct()
 *     {
 *         $this->implicationsApi = new ImplicationsApi(null, $this->getApiConfig());
 *     }
 *
 *     public function getImplications(array $rules): array
 *     {
 *         return $this->executeOperationWithErrorHandling(
 *             fn() => $this->implicationsApi->postShippingRulesImplications(...),
 *             'postShippingRulesImplications'
 *         );
 *     }
 * }
 * ```
 *
 * @see \MyParcelNL\Pdk\SdkApi\Service\AbstractSdkApiService
 * @see \MyParcelNL\Sdk\Client\Generated\CoreApiPrivate\Configuration
 */
abstract class AbstractCoreApiPrivateService extends AbstractSdkApiService
{
    /**
     * Get a configured CoreAPI Private configuration object.
     *
     * Factory: builds a fresh Configuration instance and delegates field-setting to
     * {@see AbstractSdkApiService::applyConfigSettings()} so that the same business logic
     * is shared with {@see AbstractSdkApiService::refreshApiConfig()}.
     *
     * @return CoreApiPrivateConfiguration The configured API configuration
     */
    public function getApiConfig(): CoreApiPrivateConfiguration
    {
        return $this->applyConfigSettings(new CoreApiPrivateConfiguration());
    }
}

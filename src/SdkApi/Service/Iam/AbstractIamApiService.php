<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\Iam;

use MyParcelNL\Pdk\SdkApi\Service\AbstractSdkApiService;
use MyParcelNL\Sdk\Client\Generated\IamApi\Configuration as IamApiConfiguration;

/**
 * Abstract base class for IAM API services.
 *
 * Provides a configured IamApiConfiguration for the OpenAPI-generated IAM client classes.
 * The IAM API handles authentication and authorization concerns, including:
 * - Account feature flags (ORDER_NOTES, DIRECT_PRINTING, etc.)
 * - Order management mode detection (ORDER_MANAGEMENT / LEGACY_ORDER_MANAGEMENT)
 * - Principal and role information
 *
 * **Usage:**
 * Extend this class to create concrete services for IAM endpoints.
 * Use {@see getApiConfig()} when instantiating IAM API client classes.
 *
 * @see \MyParcelNL\Pdk\SdkApi\Service\Iam\WhoamiService
 * @see \MyParcelNL\Sdk\Client\Generated\IamApi\Api\DefaultApi
 */
abstract class AbstractIamApiService extends AbstractSdkApiService
{
    /**
     * The IAM acceptance host URL.
     */
    private const IAM_URL_ACCEPTANCE = 'https://iam.acceptance.api.myparcel.nl';

    /**
     * Get a configured IAM API configuration object.
     *
     * Builds a Configuration instance with:
     * - Access token (base64-encoded API key)
     * - User agent string (platform + PDK + PHP versions)
     * - Host URL (conditional: acceptance or production)
     *
     * @return IamApiConfiguration
     */
    public function getApiConfig(): IamApiConfiguration
    {
        $config = new IamApiConfiguration();
        $apiKey = $this->getApiKey();
        $config->setAccessToken($apiKey ? base64_encode($apiKey) : '');
        $config->setUserAgent($this->getUserAgent());

        if ($this->isAcceptanceEnvironment()) {
            $config->setHost(self::IAM_URL_ACCEPTANCE);
        }

        return $config;
    }
}

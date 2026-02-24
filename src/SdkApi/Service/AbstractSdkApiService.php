<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service;

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Sdk\Client\Generated\CoreApi\ApiException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Abstract base class for all OpenAPI SDK services.
 *
 * This class provides common functionality for interacting with MyParcel APIs via the OpenAPI-generated
 * SDK client classes. It handles authentication, environment detection, user agent construction,
 * error handling, and logging.
 *
 * This is the replacement for the legacy {@see \MyParcelNL\Pdk\Api\Service\AbstractApiService} pattern,
 * which used ClientAdapterInterface and custom Request/Response objects.
 *
 * **Responsibilities:**
 * - API key retrieval from settings
 * - User agent string construction (platform + PDK version + PHP version)
 * - Environment detection (production vs acceptance)
 * - Error handling wrapper for OpenAPI operations
 * - Request/response logging
 *
 * **Usage:**
 * Extend this class in namespace-specific abstracts (e.g., AbstractCoreApiService), then create
 * concrete service classes for specific business domains (e.g., ContractDefinitionsService).
 *
 * @see \MyParcelNL\Pdk\SdkApi\Service\CoreApi\CoreApiService
 * @see \MyParcelNL\Pdk\SdkApi\Service\CoreApi\Capabilities\ContractDefinitionsService
 */
abstract class AbstractSdkApiService
{
    /**
     * @return mixed a generated Configuration clas appropiate to the API you're using
     */
    abstract function getApiConfig();

    /**
     * Get the API key from account settings.
     *
     * @return null|string The unformatted API key, or null if not set
     */
    protected function getApiKey(): ?string
    {
        return Settings::get(AccountSettings::API_KEY, AccountSettings::ID);
    }

    /**
     * Build a user agent string for API requests.
     *
     * Constructs a string in the format "Platform/Version Platform/Version ...",
     * e.g., "MyParcelNL-PDK/1.0.0 php/8.0.0"
     *
     * @return string The user agent string
     */
    protected function getUserAgent(): string
    {
        $userAgentStrings = [];
        /**
         * @var array|null $definedUserAgents
         */
        $definedUserAgents = Pdk::get('userAgent');
        $userAgents       = array_merge(
            $definedUserAgents ?? [],
            [
                'MyParcelNL-PDK' => Pdk::get('pdkVersion'),
                'php'            => PHP_VERSION,
            ]
        );

        foreach ($userAgents as $platform => $version) {
            $userAgentStrings[] = sprintf('%s/%s', $platform, $version);
        }

        return implode(' ', $userAgentStrings);
    }

    /**
     * Check if the acceptance environment is active.
     *
     * Reads the environment setting from the settings repository. If set to acceptance,
     * API services should use the acceptance API URL instead of production.
     *
     * @return bool True if acceptance environment is active, false otherwise
     */
    protected function isAcceptanceEnvironment(): bool
    {
        // Check if we're in acceptance mode via database
        try {
            /** @var PdkSettingsRepositoryInterface $settingsRepository */
            $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);
            $accountSettings = $settingsRepository->all()->account;

            if ($accountSettings->environment === Config::ENVIRONMENT_ACCEPTANCE) {
                Logger::debug('Using acceptance API URL');
                return true;
            }
        } catch (Throwable $e) {
            Logger::error('Error checking environment, assuming production', [
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * Execute an OpenAPI operation with error handling and logging.
     *
     * Wraps an OpenAPI SDK operation callable with try-catch for ApiException,
     * logs the request and response (debug on success, error on failure),
     * and re-throws exceptions after logging.
     *
     * @param callable $operation The OpenAPI operation to execute
     * @param string   $name      A descriptive name for logging purposes
     *
     * @return mixed The result of the OpenAPI operation if successful (specific generated Response class instance)
     * @throws \MyParcelNL\Sdk\Client\Generated\CoreApi\ApiException Re-thrown after logging
     * @throws \Throwable Any other exception that occurs
     */
    protected function executeOperationWithErrorHandling(callable $operation, string $name)
    {
        $logContext = [
            'operation' => $name
        ];

        try {
            /** @var ResponseInterface $response */
            $response = $operation();
            $body     = (string) $response->getBody();
            $logContext['response'] = [
                'code' => $response->getStatusCode(),
                'body' => $body ? json_decode($body, true) : null,
            ];
            Logger::debug('Successfully sent request', $logContext);
        } catch (ApiException $e) {
            // Handle OpenAPI-specific ApiException with detailed context
            Logger::error(
                'An exception was thrown while sending request',
                array_replace($logContext, [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'responseBody' => $e->getResponseBody(),
                    'responseHeaders' => $e->getResponseHeaders(),
                ])
            );

            // Re-throw after logging
            throw $e;
        } catch (Throwable $e) {
            // Handle any other exceptions without OpenAPI-specific methods
            Logger::error(
                'An exception was thrown while sending request',
                array_replace($logContext, [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                ])
            );

            // Re-throw after logging
            throw $e;
        }

        return $response;
    }
}

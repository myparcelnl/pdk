<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\SdkApi\Middleware\LoggingMiddleware;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
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
 * - Guzzle client factory with logging middleware
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
     * Create a HandlerStack with middleware.
     *
     * This method can be overridden in child classes to add custom middleware
     * to the Guzzle client handler stack. By default, it includes logging middleware.
     *
     * **Example override:**
     * ```php
     * protected function createGuzzleClientHandlerStack(): HandlerStack
     * {
     *     $stack = parent::createGuzzleClientHandlerStack();
     *     $stack->push($myCustomMiddleware);
     *     return $stack;
     * }
     * ```
     *
     * @return HandlerStack The configured handler stack
     */
    protected function createGuzzleClientHandlerStack(): HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push(LoggingMiddleware::forApiRequests());

        return $stack;
    }

    /**
     * Create a Guzzle HTTP client pre-configured with middleware.
     *
     * All API classes that accept a `?ClientInterface` as their first constructor
     * argument should receive the client produced here so that every request and
     * response is automatically logged at the transport layer.
     *
     * Uses {@see createGuzzleClientHandlerStack()} to build the middleware stack, which can
     * be overridden in child classes to add custom middleware.
     *
     * @return Client
     */
    protected function createGuzzleClient(): Client
    {
        return new Client(['handler' => $this->createGuzzleClientHandlerStack()]);
    }
}

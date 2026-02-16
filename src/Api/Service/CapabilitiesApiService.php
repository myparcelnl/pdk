<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use RuntimeException;

/**
 * Service for communicating with the Capabilities API
 */
class CapabilitiesApiService extends AbstractApiService
{
    private const CAPABILITIES_ACCEPT_HEADER = 'application/json;charset=utf-8;version=2.0';

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        $baseUrl = $this->baseUrl ?? Pdk::get('capabilitiesServiceUrl');

        if (! $baseUrl) {
            throw new RuntimeException('Capabilities service URL is not configured');
        }

        return $baseUrl;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        $apiKey = Settings::get(AccountSettings::API_KEY, AccountSettings::ID);

        if (! $apiKey) {
            throw new RuntimeException('API key is not configured');
        }

        return [
            'Authorization' => sprintf('bearer %s', base64_encode($apiKey)),
            'Accept'        => self::CAPABILITIES_ACCEPT_HEADER,
            'User-Agent'    => $this->getUserAgentHeader(),
        ];
    }
}

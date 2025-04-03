<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;

/**
 * Service for communicating with the Addresses microservice
 */
class AddressesApiService extends AbstractApiService
{
    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        $baseUrl = $this->baseUrl ?? Pdk::get('addressesServiceUrl');

        if (!$baseUrl) {
            throw new \RuntimeException('Addresses service URL is not configured');
        }

        return $baseUrl;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        $apiKey = Settings::get(AccountSettings::API_KEY, AccountSettings::ID);

        if (!$apiKey) {
            throw new \RuntimeException('API key is not configured');
        }

        return [
            'Authorization' => sprintf('bearer %s', base64_encode($apiKey)),
            'User-Agent'    => $this->getUserAgentHeader(),
        ];
    }

    /**
     * @return string
     */
    protected function getUserAgentHeader(): string
    {
        $userAgentStrings = [];
        $userAgents       = array_merge(
            Pdk::get('userAgent') ?? [],
            [
                'MyParcelNL-PDK' => Pdk::get('pdkVersion'),
                'php'            => PHP_VERSION,
            ]
        );

        foreach ($userAgents as $platform => $version) {
            if ($version) {
                $userAgentStrings[] = sprintf('%s/%s', $platform, $version);
            }
        }

        return implode(' ', $userAgentStrings);
    }
}

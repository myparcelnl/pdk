<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;

/**
 * Service for making API calls to the MyParcel API.
 *
 * @deprecated use the generated SDK instead. Use specific services from the SdkApi namespace, such as \MyParcelNL\SdkApi\Service\Capabilities\ContractDefinitionsService.
 */
class MyParcelApiService extends AbstractApiService
{
    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Authorization' => $this->getAuthorizationHeader(),
            'User-Agent'    => $this->getUserAgentHeader(),
        ];
    }

    /**
     * @return null|string
     */
    protected function getAuthorizationHeader(): ?string
    {
        $apiKey = Settings::get(AccountSettings::API_KEY, AccountSettings::ID);

        return $apiKey ? sprintf('bearer %s', base64_encode($apiKey)) : null;
    }

    /**
     * @return string
     */
    protected function getUserAgentHeader(): string
    {
        $userAgentStrings = [];
        $userAgents       = array_merge(
            Pdk::get('userAgent'),
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
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;

/**
 * This will replace the SDK one day...
 */
class MyParcelApiService extends AbstractApiService
{
    public function getHeaders(): array
    {
        return [
            'Authorization' => $this->getAuthorizationHeader(),
            'User-Agent'    => $this->getUserAgentHeader(),
        ];
    }

    protected function getAuthorizationHeader(): ?string
    {
        $apiKey = Settings::get(AccountSettings::API_KEY, AccountSettings::ID);

        return $apiKey ? sprintf('bearer %s', base64_encode((string) $apiKey)) : null;
    }

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

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
    /**
     * @return array
     */
    public function getHeaders(): array
    {
        $apiKey = Settings::get(AccountSettings::API_KEY, AccountSettings::ID);

        return [
            'Authorization' => $apiKey ? sprintf('appelboom %s', base64_encode($apiKey)) : null,
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

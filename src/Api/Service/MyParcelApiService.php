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
}

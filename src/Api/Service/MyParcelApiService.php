<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Base\Support\ApiFeatureFlags;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;

/**
 * Service for making API calls to the MyParcel API.
 *
 * @deprecated use the generated SDK instead. Use specific services from the SdkApi namespace, such as those in the MyParcelNL\Pdk\SdkApi\Service\CoreApi namespace.
 */
class MyParcelApiService extends AbstractApiService
{
    /**
     * Headers sent on every legacy MyParcel API request.
     *
     * {@see AbstractApiService::doRequest()} merges these with the per-request headers, so adding the
     * feature flags here covers the shipments and orders v1 endpoints in one place. That merge favours
     * the per-request headers, so a request that sets the same header keeps its own value.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        // Union rather than array_merge, so these headers win if a flag ever shares a name.
        return [
            'Authorization' => $this->getAuthorizationHeader(),
            'User-Agent'    => $this->getUserAgentHeader(),
        ] + ApiFeatureFlags::getHeaders();
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

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Sdk\Model\Capabilities\CapabilitiesMapper;
use MyParcelNL\Sdk\Model\Capabilities\CapabilitiesRequest;
use MyParcelNL\Sdk\Model\Capabilities\CapabilitiesResponse;
use MyParcelNL\Sdk\Services\CoreApi\CapabilitiesClientInterface;
use MyParcelNL\Sdk\Services\CoreApi\ShipmentApiFactory;

final class PdkCapabilitiesClient implements CapabilitiesClientInterface
{
    /**
     * @var \MyParcelNL\Sdk\Model\Capabilities\CapabilitiesMapper
     */
    private $mapper;

    public function __construct(?CapabilitiesMapper $mapper = null)
    {
        $this->mapper = $mapper ?? new CapabilitiesMapper();
    }

    public function getCapabilities(CapabilitiesRequest $request): CapabilitiesResponse
    {
        $apiKey  = Settings::get(AccountSettings::API_KEY, AccountSettings::ID);
        $baseUrl = Pdk::get('capabilitiesServiceUrl');

        $userAgent = $this->createUserAgentHeader();
        $api       = ShipmentApiFactory::make($apiKey, $baseUrl, $userAgent);
        $coreReq   = $this->mapper->mapToCoreApi($request);
        $coreRes   = $api->postCapabilities($userAgent, $coreReq);

        return $this->mapper->mapFromCoreApi($coreRes);
    }

    private function createUserAgentHeader(): string
    {
        $userAgentStrings = [];
        $userAgents       = array_merge(
            (array) (Pdk::get('userAgent') ?? []),
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

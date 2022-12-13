<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use Composer\InstalledVersions;
use GuzzleHttp\Client;
use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Base\Pdk;

/**
 * This will replace the SDK one day...
 */
class MyParcelApiService extends AbstractApiService
{
    private const DEFAULT_CONFIG = [
        'apiKey'     => null,
        'baseUrl'    => null,
        'httpClient' => Client::class,
        'userAgent'  => [],
    ];

    /**
     * @var null|string
     */
    private $apiKey;

    /**
     * @var array
     */
    private $userAgent;

    /**
     * @param  null|array                                         $config
     * @param  \MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface $clientAdapter
     */
    public function __construct(?array $config, ClientAdapterInterface $clientAdapter)
    {
        $config += self::DEFAULT_CONFIG;

        $this->baseUrl   = $config['baseUrl'] ?? $this->baseUrl;
        $this->apiKey    = $config['apiKey'] ?? null;
        $this->userAgent = $config['userAgent'] ?? [];

        parent::__construct($clientAdapter);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Authorization' => $this->apiKey ? sprintf('appelboom %s', base64_encode($this->apiKey)) : null,
            'User-Agent'    => $this->getUserAgentHeader(),
        ];
    }

    /**
     * @return string
     */
    protected function getUserAgentHeader(): string
    {
        $userAgentStrings = [];
        $userAgents       = [
                'MyParcelNL-PDK' => InstalledVersions::getPrettyVersion(Pdk::PACKAGE_NAME),
                'php'            => PHP_VERSION,
            ] + $this->userAgent;

        foreach ($userAgents as $platform => $version) {
            $userAgentStrings[] = sprintf('%s/%s', $platform, $version);
        }

        return implode(' ', $userAgentStrings);
    }
}

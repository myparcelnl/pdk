<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use Composer\InstalledVersions;
use GuzzleHttp\Client;

/**
 * This will replace the SDK one day...
 */
class MyParcelApiService extends AbstractApiService
{
    private const PACKAGE_NAME     = 'myparcelnl/pdk';
    private const DEFAULT_BASE_URL = 'https://api.myparcel.nl';
    private const DEFAULT_CONFIG   = [
        'baseUrl'   => self::DEFAULT_BASE_URL,
        'client'    => Client::class,
        'userAgent' => null,
    ];

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var array
     */
    private $userAgent;

    /**
     * @param  array $config
     */
    public function __construct(array $config = [])
    {
        $config += self::DEFAULT_CONFIG;

        $this->httpClient = new $config['client']();
        $this->baseUrl    = $config['baseUrl'];
        $this->apiKey     = $config['apiKey'];
        $this->userAgent  = $config['userAgent'];
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'authorization' => sprintf('appelboom %s', base64_encode($this->apiKey)),
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
            [$this->userAgent],
            ['MyParcel-PDK' => InstalledVersions::getPrettyVersion(self::PACKAGE_NAME)],
            ['php' => PHP_VERSION]
        );

        foreach ($userAgents as $key => $value) {
            if (is_int($key)) {
                $userAgentStrings[] = $value;
                continue;
            }

            $userAgentStrings[] = $key . '/' . $value;
        }

        return implode(' ', $userAgentStrings);
    }
}

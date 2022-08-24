<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use Composer\InstalledVersions;
use GuzzleHttp\Client;
use MyParcelNL\Pdk\Base\Pdk;

/**
 * This will replace the SDK one day...
 */
class MyParcelApiService extends AbstractApiService
{
    private const DEFAULT_BASE_URL = 'https://api.myparcel.nl';
    private const DEFAULT_CONFIG   = [
        'apiKey'     => '',
        'baseUrl'    => self::DEFAULT_BASE_URL,
        'httpClient' => Client::class,
        'userAgent'  => null,
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

        $this->httpClient = new $config['httpClient']() ?? null;
        $this->baseUrl    = $config['baseUrl'] ?? null;
        $this->apiKey     = $config['apiKey'] ?? null;
        $this->userAgent  = $config['userAgent'] ?? null;
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
        $userAgentValue   = array_values($this->userAgent);
        $userAgentStrings = [
            array_key_first($this->userAgent) => array_shift($userAgentValue),
            'MyParcel-PDK'                    => InstalledVersions::getPrettyVersion(Pdk::PACKAGE_NAME),
            'php'                             => PHP_VERSION,
        ];

        foreach ($userAgentStrings as $platform => $version) {
            $userAgentStrings[] = sprintf('%s/%s', $platform, $version);
            unset($userAgentStrings[$platform]);
        }

        return implode(' ', $userAgentStrings);
    }
}

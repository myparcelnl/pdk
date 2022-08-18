<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use GuzzleHttp\Client;

/**
 * This will replace the SDK one day...
 */
class MyParcelApiService extends AbstractApiService
{
    private const DEFAULT_BASE_URL = 'https://api.myparcel.nl';
    private const DEFAULT_CONFIG   = [
        'baseUrl' => self::DEFAULT_BASE_URL,
        'client'  => Client::class,
    ];

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param  array $config
     */
    public function __construct(array $config = [])
    {
        $config += self::DEFAULT_CONFIG;

        $this->httpClient = new $config['client']();
        $this->baseUrl    = $config['baseUrl'];
        $this->apiKey     = $config['apiKey'];
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
    protected function getHeaders(): array
    {
        return [
            'authorization' => sprintf('appelboom %s', base64_encode($this->apiKey)),
        ];
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use MyParcelNL\Pdk\Api\Service\AbstractApiService;

class MockApiService extends AbstractApiService
{
    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    public $mock;

    public function __construct()
    {
        $mock   = new MockHandler();
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $this->httpClient = $client;
        $this->mock       = $mock;
    }

    public function getBaseUrl(): string
    {
        return 'MOCK_API';
    }

    protected function getRequestHeaders(): array
    {
        return [];
    }
}

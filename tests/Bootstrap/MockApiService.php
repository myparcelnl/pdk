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
    private $mock;

    public function __construct()
    {
        $mock   = new MockHandler();
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $this->httpClient = $client;
        $this->mock       = $mock;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return 'API';
    }

    /**
     * @return \GuzzleHttp\Handler\MockHandler
     */
    public function getMock(): MockHandler
    {
        return $this->mock;
    }
}

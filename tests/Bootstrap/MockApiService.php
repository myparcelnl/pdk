<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Api\Service\AbstractApiService;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Tests\Api\Guzzle7ClientAdapter;

class MockApiService extends AbstractApiService
{
    /**
     * @var \GuzzleHttp\Handler\MockHandler
     */
    private $mock;

    /**
     * @param  Response|Response[]                            $mockQueue
     * @param  \MyParcelNL\Pdk\Tests\Api\Guzzle7ClientAdapter $clientAdapter
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct($mockQueue = [], Guzzle7ClientAdapter $clientAdapter)
    {
        $mock   = new MockHandler();
        $client = new Client(['handler' => HandlerStack::create($mock)]);

        $clientAdapter->setClient($client);

        parent::__construct($clientAdapter);

        $this->mock = $mock;

        foreach (Arr::wrap($mockQueue) as $response) {
            $this->mock->append($response);
        }
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

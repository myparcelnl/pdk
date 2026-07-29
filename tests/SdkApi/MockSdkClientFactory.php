<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\SdkApi;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use MyParcelNL\Pdk\SdkApi\Contract\SdkClientFactoryInterface;

/**
 * Test transport: answers from the shared {@see MockSdkApiHandler} queue instead of the network.
 *
 * Bound once in MockPdkConfig, which covers every SdkApi service — current and future — the same
 * way binding ClientAdapterInterface covers every legacy API service. Without it, SdkApi services
 * would send real requests to the live API from unit tests.
 *
 * The service's middleware stack is kept intact and only its handler is replaced, so middleware
 * such as request logging and the capabilities Accept header still runs. Enqueue responses with:
 *
 *   MockSdkApiHandler::enqueue(new ExampleContractDefinitionsResponse());
 */
class MockSdkClientFactory implements SdkClientFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function create(HandlerStack $stack): Client
    {
        $stack->setHandler(MockSdkApiHandler::getHandler());

        return new Client(['handler' => $stack]);
    }
}

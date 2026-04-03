<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use GuzzleHttp\Client;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;

/**
 * Test double for CapabilitiesService.
 *
 * Replaces the real Guzzle transport with the shared MockSdkApiHandler queue
 * while keeping the full real middleware stack (LoggingMiddleware + the
 * Accept-header override specific to CapabilitiesService).
 *
 * Registered in MockPdkConfig so the DI container injects this class wherever
 * CapabilitiesService is type-hinted (e.g. CarrierCapabilitiesRepository).
 *
 * Enqueue responses in tests before the code under test runs:
 *   MockSdkApiHandler::enqueue(new ExampleContractDefinitionsResponse());
 */
class MockCapabilitiesService extends CapabilitiesService
{
    /**
     * @return \GuzzleHttp\Client
     */
    protected function createGuzzleClient(): Client
    {
        $stack = $this->createGuzzleClientHandlerStack();
        $stack->setHandler(MockSdkApiHandler::getHandler());

        return new Client(['handler' => $stack]);
    }
}

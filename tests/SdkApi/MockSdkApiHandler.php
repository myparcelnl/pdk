<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\SdkApi;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

/**
 * Centrally managed GuzzleHttp MockHandler for all SdkApi service mocks.
 *
 * Tests enqueue GuzzleHttp\Psr7\Response instances (or SdkJsonResponse subclasses)
 * before executing code that triggers SdkApi calls:
 *
 *   MockSdkApiHandler::enqueue(new ExampleContractDefinitionsResponse());
 *
 * UsesSdkApiMock resets the handler before and after each test so unconsumed
 * responses never bleed across tests.
 */
final class MockSdkApiHandler
{
    /**
     * @var null|\GuzzleHttp\Handler\MockHandler
     */
    private static $handler = null;

    /**
     * Append one or more responses to the queue.
     *
     * @param  \GuzzleHttp\Psr7\Response ...$responses
     *
     * @return void
     */
    public static function enqueue(Response ...$responses): void
    {
        self::getHandler()->append(...$responses);
    }

    /**
     * Clear the handler's response queue in-place.
     * Called by UsesSdkApiMock before and after each test.
     *
     * Must clear the existing instance rather than replacing it, because
     * MockCapabilitiesService captures the handler reference at construction
     * time (via HandlerStack). Replacing the static would leave the Client
     * pointing at a stale, empty handler.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::getHandler()->reset();
    }

    /**
     * Return the shared MockHandler, lazily initialising it on first access.
     *
     * @return \GuzzleHttp\Handler\MockHandler
     */
    public static function getHandler(): MockHandler
    {
        if (null === self::$handler) {
            self::$handler = new MockHandler();
        }

        return self::$handler;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use MyParcelNL\Pdk\SdkApi\Contract\SdkClientFactoryInterface;

/**
 * Production transport: a plain Guzzle client that really sends the request.
 *
 * Bound by default, so SdkApi services behave exactly as they did when they built the client
 * themselves. Swap the binding to route every SdkApi service somewhere else.
 */
final class GuzzleSdkClientFactory implements SdkClientFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function create(HandlerStack $stack): Client
    {
        return new Client(['handler' => $stack]);
    }
}

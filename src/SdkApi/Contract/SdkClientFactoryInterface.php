<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Contract;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

/**
 * Builds the HTTP client the generated SDK API classes send their requests through.
 *
 * This is the SdkApi counterpart to {@see \MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface}:
 * the transport is injected rather than constructed inline, so it can be swapped in one place
 * for every SdkApi service at once. Tests bind an implementation that answers from a queue
 * instead of the network, which is what keeps them from calling the live API.
 *
 * Implementations receive the middleware stack the service already assembled (request logging,
 * plus anything a service adds of its own such as the capabilities Accept header) and must keep
 * it in place, so swapping the transport does not quietly drop middleware.
 */
interface SdkClientFactoryInterface
{
    /**
     * Wrap the given middleware stack in a client ready for the SDK API classes.
     *
     * @param  \GuzzleHttp\HandlerStack $stack Middleware stack assembled by the calling service.
     *
     * @return \GuzzleHttp\Client
     */
    public function create(HandlerStack $stack): Client;
}

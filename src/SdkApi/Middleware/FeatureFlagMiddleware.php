<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Middleware;

use GuzzleHttp\Promise\PromiseInterface;
use MyParcelNL\Pdk\Base\Support\ApiFeatureFlags;
use Psr\Http\Message\RequestInterface;

/**
 * Guzzle middleware that puts the configured API feature flags on every outgoing request.
 *
 * Pushed onto the same handler stack as the logging middleware, so every request made through a
 * generated SDK client carries the flags without each service having to remember them. Existing
 * headers are left alone; only the configured flags are added.
 *
 * **Usage:**
 * ```php
 * $stack = HandlerStack::create();
 * $stack->push(FeatureFlagMiddleware::forApiRequests());
 * $client = new Client(['handler' => $stack]);
 * ```
 */
class FeatureFlagMiddleware
{
    /**
     * Create a Guzzle middleware callable that adds the feature flag headers.
     *
     * Follows the Guzzle double-callable middleware convention:
     * the outer callable receives the next handler, the inner callable
     * receives the request and options and returns a promise.
     *
     * @return callable(callable): callable
     */
    public static function forApiRequests(): callable
    {
        return static function (callable $handler): callable {
            return static function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
                foreach (ApiFeatureFlags::getHeaders() as $name => $value) {
                    $request = $request->withHeader($name, $value);
                }

                return $handler($request, $options);
            };
        };
    }
}

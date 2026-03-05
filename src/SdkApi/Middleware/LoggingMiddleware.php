<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Middleware;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use MyParcelNL\Pdk\Facade\Logger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Guzzle middleware for logging API requests and responses.
 *
 * Intercepts all HTTP traffic at the transport layer to provide:
 * - Debug logging of outgoing requests (method + URI)
 * - Debug logging of successful responses (status code + decoded body)
 * - Error logging of failed requests (error message, code, response body/headers)
 *
 * **Usage:**
 * ```php
 * $stack = HandlerStack::create();
 * $stack->push(LoggingMiddleware::forApiRequests());
 * $client = new Client(['handler' => $stack]);
 * ```
 */
class LoggingMiddleware
{
    /**
     * Create a Guzzle middleware callable that logs requests and responses.
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
                Logger::debug('Sending API request', [
                    'method' => $request->getMethod(),
                    'uri'    => (string) $request->getUri(),
                ]);


                return $handler($request, $options)->then(
                    static function (ResponseInterface $response): ResponseInterface {
                        $body    = (string) $response->getBody();
                        $decoded = $body ? json_decode($body, true) : null;

                        Logger::debug('Received API response', [
                            'status' => $response->getStatusCode(),
                            'body'   => $decoded,
                        ]);

                        // Rewind so the SDK can still read the body after logging.
                        $response->getBody()->rewind();

                        return $response;
                    },
                    static function (Throwable $e): void {
                        $context = [
                            'error' => $e->getMessage(),
                            'code'  => $e->getCode(),
                        ];

                        if ($e instanceof RequestException && $e->getResponse()) {
                            $body                      = (string) $e->getResponse()->getBody();
                            $context['responseBody']    = $body ? json_decode($body, true) : null;
                            $context['responseHeaders'] = $e->getResponse()->getHeaders();
                        }

                        Logger::error('API request failed', $context);

                        throw $e;
                    }
                );
            };
        };
    }
}

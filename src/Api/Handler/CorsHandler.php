<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Handler;

use Fruitcake\Cors\CorsService;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proxy handler for CORS related functionality
 */
class CorsHandler
{
    /**
     * @var CorsService
     */
    private $cors;

    /**
     * @param  array $options
     */
    public function __construct(array $options = [])
    {
        $allowedOrigins = Pdk::get('allowedProxyOrigins') ?? Pdk::get('allowedProxyHosts');

        $defaultOptions = [
            'allowedOrigins'      => (array) $allowedOrigins,
            'allowedMethods'      => ['GET', 'POST', 'OPTIONS'],
            'allowedHeaders'      => ['Content-Type', 'Accept', 'Authorization', 'Origin'],
            'exposedHeaders'      => [],
            'maxAge'              => 0,
            'supportsCredentials' => false,
        ];

        $this->cors = new CorsService(array_merge($defaultOptions, $options));
    }

    /**
     * Proxy all method calls to the CorsService
     *
     * @param  string $name
     * @param  array  $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->cors->$name(...$arguments);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response $response
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addCorsHeaders(Request $request, Response $response): Response
    {
        return $this->cors->addActualRequestHeaders($response, $request);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function handlePreflightRequest(Request $request): ?Response
    {
        if ($request->getMethod() !== 'OPTIONS') {
            return null;
        }

        return $this->cors->handlePreflightRequest($request);
    }
}

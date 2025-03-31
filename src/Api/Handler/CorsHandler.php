<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Handler;

use Fruitcake\Cors\CorsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handler for CORS related functionality
 */
class CorsHandler
{
    /**
     * @var CorsService
     */
    private $cors;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->cors = new CorsService($options);
    }

    /**
     * Handle CORS preflight request
     *
     * @param Request $request
     * @return Response|null
     */
    public function handlePreflight(Request $request): ?Response
    {
        return $this->cors->handlePreflightRequest($request);
    }

    /**
     * Add CORS headers to response
     *
     * @param Request  $request
     * @param Response $response
     */
    public function addCorsHeaders(Request $request, Response $response): void
    {
        if ($this->cors->isOriginAllowed($request)) {
            $this->cors->addActualRequestHeaders($response, $request);
        }
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Endpoint\Contract;

use MyParcelNL\Pdk\App\Endpoint\EndpointRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service interface for handling PDK endpoint requests.
 *
 * Uses EndpointRegistry for type-safe request routing.
 *
 * @since 3.1.0
 */
interface EndpointServiceInterface
{
    /**
     * Handle an endpoint request.
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  \MyParcelNL\Pdk\App\Endpoint\EndpointRegistry   $endpoint
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleRequest(Request $request, EndpointRegistry $endpoint): Response;
}

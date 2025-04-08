<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Controller;

use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\Api\Request\ProxyRequest;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for handling requests to the Addresses microservice
 */
class AddressesProxyController
{
    /**
     * @var \MyParcelNL\Pdk\Api\Service\AddressesApiService
     */
    private $addressesApiService;

    /**
     * @var \MyParcelNL\Pdk\Api\Handler\CorsHandler
     */
    private $corsHandler;

    /**
     * @param  AddressesApiService $addressesApiService
     * @param  CorsHandler         $corsHandler
     */
    public function __construct(
        AddressesApiService $addressesApiService,
        CorsHandler         $corsHandler
    ) {
        $this->addressesApiService = $addressesApiService;
        $this->corsHandler         = $corsHandler;
    }

    /**
     * Handles a proxy request
     *
     * @param  Request $request
     * @param  string  $path
     *
     * @return Response
     */
    public function proxy(Request $request, string $path): Response
    {
        // Handle CORS preflight request
        $preflightResponse = $this->corsHandler->handlePreflightRequest($request);
        if ($preflightResponse instanceof Response) {
            return $preflightResponse;
        }

        // Check if origin is allowed
        $origin       = $request->headers->get('Origin');
        $allowedHosts = Pdk::get('allowedProxyHosts');

        if ($origin && ! in_array($origin, $allowedHosts) && ! in_array('*', $allowedHosts)
            && ! in_array(
                'self',
                $allowedHosts
            )) {
            return new Response('Unauthorized origin', Response::HTTP_FORBIDDEN);
        }

        // Build the request for the microservice
        $proxyRequest = new ProxyRequest(
            $request->getMethod(),
            $path,
            $request->getContent(),
            $request->query->all(),
            $request->headers->all()
        );

        // Send the request and return the response
        try {
            $response     = $this->addressesApiService->doRequest($proxyRequest);
            $jsonResponse = new Response(
                $response->getBody(),
                $response->getStatusCode(),
                ['Content-Type' => 'application/json']
            );
            $this->corsHandler->addCorsHeaders($request, $jsonResponse);

            return $jsonResponse;
        } catch (ApiException $e) {
            $errorResponse = new Response(
                json_encode(['error' => $e->getMessage()]),
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR,
                ['Content-Type' => 'application/json']
            );
            $this->corsHandler->addCorsHeaders($request, $errorResponse);

            return $errorResponse;
        }
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Controller;

use Fruitcake\Cors\CorsService;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Request\ProxyRequest;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @var array
     */
    private $corsOptions;

    /**
     * @param  \MyParcelNL\Pdk\Api\Service\AddressesApiService $addressesApiService
     */
    public function __construct(AddressesApiService $addressesApiService)
    {
        $this->addressesApiService = $addressesApiService;
        $this->corsOptions = [
            'allowedOrigins' => Pdk::get('allowedProxyOrigins'),
            'allowedMethods' => ['GET', 'OPTIONS'],
            'allowedHeaders' => ['Content-Type', 'Accept', 'Accept-Language', 'Authorization'],
            'exposedHeaders' => [],
            'maxAge' => 0,
            'supportsCredentials' => false,
        ];
    }

    /**
     * Handles a proxy request
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  string                                    $path
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function proxy(Request $request, string $path): Response
    {
        // Handle CORS preflight request
        if ($request->getMethod() === 'OPTIONS') {
            $response = new Response('', Response::HTTP_NO_CONTENT);
            $this->addCorsHeaders($request, $response);
            return $response;
        }

        // Check if origin is allowed
        $origin = $request->headers->get('Origin');
        $allowedOrigins = $this->corsOptions['allowedOrigins'];
        
        if ($origin && !in_array($origin, $allowedOrigins) && !in_array('*', $allowedOrigins) && !in_array('self', $allowedOrigins)) {
            Logger::warning('Unauthorized origin attempted to access proxy', ['origin' => $origin]);
            return new JsonResponse(['error' => 'Unauthorized origin'], Response::HTTP_FORBIDDEN);
        }

        // Build the request for the microservice
        $proxyRequest = new ProxyRequest(
            $request->getMethod(),
            $path,
            $request->getContent(),
            $request->query->all(),
            $this->filterHeaders($request->headers->all())
        );

        // Send the request and return the response
        try {
            $response = $this->addressesApiService->doRequest($proxyRequest);
            $jsonResponse = new JsonResponse(
                json_decode($response->getBody(), true),
                $response->getStatusCode()
            );

            // Add CORS headers to response
            $this->addCorsHeaders($request, $jsonResponse);
            return $jsonResponse;
        } catch (ApiException $e) {
            Logger::error('Error in addresses proxy: ' . $e->getMessage());

            $errorResponse = new JsonResponse(
                ['error' => $e->getMessage()],
                $e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );

            $this->addCorsHeaders($request, $errorResponse);
            return $errorResponse;
        }
    }

    /**
     * Add CORS headers to response
     *
     * @param Request  $request
     * @param Response $response
     */
    private function addCorsHeaders(Request $request, Response $response): void
    {
        $origin = $request->headers->get('Origin');
        
        if (!$origin) {
            return;
        }

        // Check if origin is allowed
        if (in_array('*', $this->corsOptions['allowedOrigins'])) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        } elseif (in_array($origin, $this->corsOptions['allowedOrigins'])) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } elseif (in_array('self', $this->corsOptions['allowedOrigins'])) {
            $response->headers->set('Access-Control-Allow-Origin', $request->getSchemeAndHttpHost());
        }

        if ($request->getMethod() === 'OPTIONS') {
            $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->corsOptions['allowedMethods']));
            $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->corsOptions['allowedHeaders']));
            
            if ($this->corsOptions['maxAge']) {
                $response->headers->set('Access-Control-Max-Age', $this->corsOptions['maxAge']);
            }
        }

        if ($this->corsOptions['supportsCredentials']) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if ($this->corsOptions['exposedHeaders']) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->corsOptions['exposedHeaders']));
        }
    }

    /**
     * Filters sensitive headers
     *
     * @param  array $headers
     *
     * @return array
     */
    private function filterHeaders(array $headers): array
    {
        $allowedHeaders = [
            'content-type',
            'accept',
            'accept-language',
            'authorization',
        ];

        $result = [];
        foreach ($headers as $name => $value) {
            $lowerName = strtolower($name);
            if (in_array($lowerName, $allowedHeaders)) {
                $result[$name] = is_array($value) && ! empty($value) ? $value[0] : $value;
            }
        }

        return $result;
    }
}

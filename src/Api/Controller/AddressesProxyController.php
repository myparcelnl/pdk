<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Controller;

use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Request\ProxyRequest;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\Api\Service\HostDetectionService;
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
     * @var \MyParcelNL\Pdk\Api\Service\HostDetectionService
     */
    private $hostDetectionService;
    
    /**
     * @param \MyParcelNL\Pdk\Api\Service\AddressesApiService $addressesApiService
     * @param \MyParcelNL\Pdk\Api\Service\HostDetectionService $hostDetectionService
     */
    public function __construct(
        AddressesApiService $addressesApiService,
        HostDetectionService $hostDetectionService
    ) {
        $this->addressesApiService = $addressesApiService;
        $this->hostDetectionService = $hostDetectionService;
    }
    
    /**
     * Handles a proxy request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $path
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function proxy(Request $request, string $path): Response
    {
        // Security check
        $securityCheck = $this->performSecurityCheck($request);
        if ($securityCheck !== true) {
            return $securityCheck;
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
            
            return new JsonResponse(
                json_decode($response->getBody(), true),
                $response->getStatusCode()
            );
        } catch (ApiException $e) {
            Logger::error('Error in addresses proxy: ' . $e->getMessage());
            
            return new JsonResponse(
                ['error' => $e->getMessage()],
                $e->getCode() ?: 500
            );
        }
    }
    
    /**
     * Performs security checks on the request
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return true|\Symfony\Component\HttpFoundation\Response
     */
    private function performSecurityCheck(Request $request)
    {
        // Host header check with dynamic detection
        $allowedHosts = $this->hostDetectionService->getAllowedHosts();
        $host = $request->headers->get('Host');
        
        if (!in_array($host, $allowedHosts)) {
            Logger::warning('Unauthorized host attempted to access proxy', ['host' => $host]);
            return new JsonResponse(['error' => 'Unauthorized host'], 403);
        }
        
        // CORS protection
        $origin = $request->headers->get('Origin');
        if ($origin) {
            $allowedOrigins = Pdk::get('allowedProxyOrigins');
            
            if (!in_array($origin, $allowedOrigins) && !in_array('*', $allowedOrigins)) {
                Logger::warning('Unauthorized origin attempted to access proxy', ['origin' => $origin]);
                return new JsonResponse(['error' => 'Unauthorized origin'], 403);
            }
        }
        
        return true;
    }
    
    /**
     * Filters sensitive headers
     *
     * @param array $headers
     *
     * @return array
     */
    private function filterHeaders(array $headers): array
    {
        $allowedHeaders = [
            'content-type',
            'accept',
            'accept-language'
        ];
        
        $result = [];
        foreach ($headers as $name => $value) {
            $lowerName = strtolower($name);
            if (in_array($lowerName, $allowedHeaders)) {
                $result[$name] = is_array($value) && !empty($value) ? $value[0] : $value;
            }
        }
        
        return $result;
    }
} 
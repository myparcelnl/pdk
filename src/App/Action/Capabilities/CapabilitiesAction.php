<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Capabilities;

use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\Api\Request\Request as ApiRequest;
use MyParcelNL\Pdk\Api\Response\CapabilitiesResponse;
use MyParcelNL\Pdk\Api\Service\CapabilitiesApiService;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CapabilitiesAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Api\Service\CapabilitiesApiService
     */
    private $apiService;

    /**
     * @param  \MyParcelNL\Pdk\Api\Service\CapabilitiesApiService $apiService
     */
    public function __construct(CapabilitiesApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $corsHandler = Pdk::get(CorsHandler::class);

        if ($request->isMethod('OPTIONS')) {
            return $corsHandler->handlePreflightRequest($request) ?? new Response();
        }

        /** @var \MyParcelNL\Pdk\Api\Response\CapabilitiesResponse $response */
        $response = $this->apiService->doRequest($this->buildRequest($request), CapabilitiesResponse::class);

        $symfonyResponse = $response->getSymfonyResponse();

        return $corsHandler->addCorsHeaders($request, $symfonyResponse);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $incomingRequest
     *
     * @return \MyParcelNL\Pdk\Api\Request\Request
     */
    public function buildRequest(Request $incomingRequest): ApiRequest
    {
        $query  = $incomingRequest->query->all();
        $path   = $query['path'] ?? '';
        $method = $incomingRequest->getMethod();

        unset($query['action'], $query['pdk_action'], $query['path']);

        return new ApiRequest([
            'method'     => $method,
            'path'       => $path ? '/' . ltrim((string) $path, '/') : '',
            'parameters' => $query,
        ]);
    }
}

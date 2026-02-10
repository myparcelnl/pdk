<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Capabilities;

use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\Api\Request\Request as ApiRequest;
use MyParcelNL\Pdk\Api\Response\CapabilitiesResponse as ProxyCapabilitiesResponse;
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

        try {
            $capabilitiesResponse = $this->apiService->doRequest($this->buildRequest($request), ProxyCapabilitiesResponse::class);
            $response             = $capabilitiesResponse->getSymfonyResponse();
        } catch (ApiException $e) {
            $response = $this->createErrorResponse($e);
        }

        return $corsHandler->addCorsHeaders($request, $response);
    }

    public function buildRequest(Request $incomingRequest): ApiRequest
    {
        $query = $incomingRequest->query->all();

        unset($query['action'], $query['pdk_action'], $query['path']);

        $contentType = $incomingRequest->headers->get('Content-Type');

        return new ApiRequest([
            'method'     => $incomingRequest->getMethod(),
            'path'       => '/shipments/capabilities',
            'parameters' => $query,
            'body'       => $incomingRequest->getContent(),
            'headers'    => $contentType ? ['Content-Type' => $contentType] : [],
        ]);
    }

    private function createErrorResponse(ApiException $exception): Response
    {
        $response = $exception->getResponse();

        return new Response(
            $response->getBody() ?: '',
            $response->getStatusCode(),
            ['Content-Type' => 'application/json']
        );
    }
}

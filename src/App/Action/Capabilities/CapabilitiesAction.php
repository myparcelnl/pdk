<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Capabilities;

use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\ApiException as CoreApiException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CapabilitiesAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService
     */
    private $capabilitiesService;

    /**
     * @param  \MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService $capabilitiesService
     */
    public function __construct(CapabilitiesService $capabilitiesService)
    {
        $this->capabilitiesService = $capabilitiesService;
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
            $body = json_decode($request->getContent(), true);

            if (! is_array($body)) {
                throw new \InvalidArgumentException('Request body must be a JSON object', 400);
            }

            $shouldFilter = $request->query->getBoolean('filterSupported', false);
            $results      = $this->capabilitiesService->getCapabilities($body, $shouldFilter);
            $response     = $this->createSymfonyResponse($results);
        } catch (Throwable $e) {
            $response = $this->createErrorResponse($e);
        }

        return $corsHandler->addCorsHeaders($request, $response);
    }

    /**
     * @param  \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2[] $results
     */
    private function createSymfonyResponse(array $results): Response
    {
        return new Response(
            json_encode(['results' => $results]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    private function createErrorResponse(Throwable $exception): Response
    {
        $statusCode = method_exists($exception, 'getCode') ? (int) $exception->getCode() : 0;
        if ($statusCode < 400 || $statusCode > 599) {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $body = null;

        if ($exception instanceof CoreApiException) {
            $responseBody = $exception->getResponseBody();
            $body         = is_string($responseBody) ? $responseBody : json_encode($responseBody);
        }

        if (! $body && method_exists($exception, 'getResponseBody')) {
            $responseBody = $exception->getResponseBody();
            $body         = is_string($responseBody) ? $responseBody : json_encode($responseBody);
        }

        if (! $body) {
            $body = json_encode(['message' => $exception->getMessage()]);
        }

        return new Response(
            $body ?: '',
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }
}

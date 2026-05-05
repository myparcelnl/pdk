<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Capabilities;

use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
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

        $payload                                = $this->getRequestData($request);
        [$sdkArgs, $shouldFilterOptions]        = $this->prepareSdkArgs($payload);

        try {
            $results  = $this->capabilitiesService->getCapabilities($sdkArgs);
            $response = $this->createSymfonyResponse($results, $shouldFilterOptions);
        } catch (Throwable $e) {
            $response = $this->createErrorResponse($e);
        }

        return $corsHandler->addCorsHeaders($request, $response);
    }

    /**
     * Pull the action's control parameter ($filterOptions) out of the payload and translate any
     * PDK-camelCase argument names to SDK-snake_case so callers don't have to know SDK conventions.
     *
     * @param  array $payload
     *
     * @return array{0: array, 1: bool} [SDK args (control flag stripped), shouldFilterOptions]
     */
    private function prepareSdkArgs(array $payload): array
    {
        $shouldFilterOptions = ! empty($payload['filterOptions']);
        unset($payload['filterOptions']);

        if (isset($payload['packageType'])) {
            $payload['package_type'] = DeliveryOptions::PACKAGE_TYPES_V2_MAP[$payload['packageType']]
                ?? $payload['packageType'];
            unset($payload['packageType']);
        }

        if (isset($payload['deliveryType'])) {
            $payload['delivery_type'] = DeliveryOptions::DELIVERY_TYPES_V2_MAP[$payload['deliveryType']]
                ?? $payload['deliveryType'];
            unset($payload['deliveryType']);
        }

        return [$payload, $shouldFilterOptions];
    }

    private function getRequestData(Request $request): array
    {
        $body     = json_decode($request->getContent(), true);
        $bodyData = is_array($body) ? ($body['data'] ?? $body) : [];
        $query    = $request->query->all();

        unset($query['action'], $query['pdk_action'], $query['path']);

        return array_replace($bodyData, $query);
    }

    /**
     * @param  \MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2[] $results
     * @param  bool                                                                                $filterOptions
     *                                                                                                Apply the registered-options allowlist to each capability's options when true. Default false
     *                                                                                                preserves the unfiltered SDK passthrough for existing callers.
     */
    private function createSymfonyResponse(array $results, bool $filterOptions = false): Response
    {
        $body = json_decode(json_encode(['results' => $results]), true);

        if ($filterOptions && isset($body['results']) && is_array($body['results'])) {
            $body['results'] = array_map(static function (array $capability): array {
                if (isset($capability['options']) && is_array($capability['options'])) {
                    $capability['options'] = Carrier::filterRegisteredOptions($capability['options']);
                }
                return $capability;
            }, $body['results']);
        }

        return new Response(
            json_encode($body),
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

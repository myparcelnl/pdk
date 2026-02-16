<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Capabilities;

use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings as AccountSettingsModel;
use MyParcelNL\Sdk\Model\Capabilities\CapabilitiesRequest;
use MyParcelNL\Sdk\Model\Capabilities\CapabilitiesResponse;
use MyParcelNL\Sdk\Services\Capabilities\CapabilitiesServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CapabilitiesAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Sdk\Services\Capabilities\CapabilitiesServiceInterface
     */
    private $apiService;

    /**
     * @param  \MyParcelNL\Sdk\Services\Capabilities\CapabilitiesServiceInterface $apiService
     */
    public function __construct(CapabilitiesServiceInterface $apiService)
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

        $payload = $this->getRequestData($request);
        $apiKey  = Settings::get(AccountSettingsModel::API_KEY, AccountSettingsModel::ID);

        if ($apiKey) {
            putenv("API_KEY={$apiKey}");
        }

        try {
            $sdkRequest = $this->createCapabilitiesRequest($payload);
            $sdkResult  = $this->apiService->get($sdkRequest);
            $response   = $this->createSymfonyResponse($sdkResult);
        } catch (Throwable $e) {
            $response = $this->createErrorResponse($e);
        }

        return $corsHandler->addCorsHeaders($request, $response);
    }

    private function getRequestData(Request $request): array
    {
        $body     = json_decode($request->getContent(), true);
        $bodyData = is_array($body) ? ($body['data'] ?? $body) : [];
        $query    = $request->query->all();

        unset($query['action'], $query['pdk_action'], $query['path']);

        return array_replace($bodyData, $query);
    }

    private function createCapabilitiesRequest(array $data): CapabilitiesRequest
    {
        $recipient = $data['recipient'] ?? null;
        $country   = $data['country'] ?? $data['country_code'] ?? $data['countryCode'] ?? null;

        if (! $country && is_array($recipient)) {
            $country = $recipient['country_code'] ?? $recipient['countryCode'] ?? null;
        }

        $request = CapabilitiesRequest::forCountry((string) $country);

        $shopId = $data['shopId'] ?? $data['shop_id'] ?? null;
        if ($shopId) {
            $request = $request->withShopId((int) $shopId);
        }

        $carrier = $data['carrier'] ?? $data['carrier_id'] ?? null;
        if ($carrier) {
            $request = $request->withCarrier((string) $carrier);
        }

        $packageType = $data['packageType'] ?? $data['package_type'] ?? null;
        if ($packageType && method_exists($request, 'withPackageType')) {
            $request = $request->withPackageType((string) $packageType);
        }

        $deliveryType = $data['deliveryType'] ?? $data['delivery_type'] ?? null;
        if ($deliveryType && method_exists($request, 'withDeliveryType')) {
            $request = $request->withDeliveryType((string) $deliveryType);
        }

        $direction = $data['direction'] ?? null;
        if ($direction && method_exists($request, 'withDirection')) {
            $request = $request->withDirection((string) $direction);
        }

        $pickup = $data['pickup'] ?? null;
        if (is_array($pickup) && method_exists($request, 'withPickup')) {
            $request = $request->withPickup($pickup);
        }

        $sender = $data['sender'] ?? null;
        if (is_array($sender)) {
            $request = $request->withSender($sender);
        }

        $physicalProperties = $data['physicalProperties'] ?? $data['physical_properties'] ?? null;
        if (is_array($physicalProperties)) {
            $request = $request->withPhysicalProperties($physicalProperties);
        }

        $options = $this->normalizeOptions($data['options'] ?? $data['shipment_options'] ?? null);
        if ($options) {
            $request = $request->withOptions($options);
        }

        return $request;
    }

    private function createSymfonyResponse(CapabilitiesResponse $response): Response
    {
        return new Response(
            json_encode([
                'data' => [
                    'package_types'     => $response->getPackageTypes(),
                    'delivery_types'    => $response->getDeliveryTypes(),
                    'shipment_options'  => $response->getShipmentOptions(),
                    'carrier'           => $response->getCarrier(),
                    'transaction_types' => $response->getTransactionTypes(),
                    'collo_max'         => $response->getColloMax(),
                ],
            ]),
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

        if (method_exists($exception, 'getResponseBody')) {
            $responseBody = $exception->getResponseBody();
            $body         = is_string($responseBody) ? $responseBody : json_encode($responseBody);
        }

        if (! $body && method_exists($exception, 'getResponse')) {
            $response = $exception->getResponse();
            if ($response && method_exists($response, 'getBody')) {
                $body = (string) $response->getBody();
            }
            if ($response && method_exists($response, 'getStatusCode')) {
                $responseStatus = (int) $response->getStatusCode();
                if ($responseStatus >= 400 && $responseStatus <= 599) {
                    $statusCode = $responseStatus;
                }
            }
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

    private function normalizeOptions($options): array
    {
        if (! is_array($options)) {
            return [];
        }

        $keys = array_keys($options);
        $isList = $keys === range(0, count($keys) - 1);

        if (! $isList) {
            return $options;
        }

        return array_fill_keys($options, null);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Capabilities;

use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings as AccountSettingsModel;
use MyParcelNL\Sdk\Model\Capabilities\CapabilitiesRequest;
use MyParcelNL\Sdk\Services\Capabilities\CapabilitiesServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $payload              = $this->getRequestData($request);
        $apiKey               = Settings::get(AccountSettingsModel::API_KEY, AccountSettingsModel::ID);
        $baseUrl              = Pdk::get('capabilitiesServiceUrl');
        $capabilitiesRequest  = $this->createCapabilitiesRequest($payload);

        $this->configureService($this->apiService, $apiKey, $baseUrl);

        $capabilitiesResponse = $this->apiService->get($capabilitiesRequest);
        $symfonyResponse      = $this->createSymfonyResponse($capabilitiesResponse);

        return $corsHandler->addCorsHeaders($request, $symfonyResponse);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    private function getRequestData(Request $request): array
    {
        $body     = json_decode($request->getContent(), true);
        $bodyData = is_array($body) ? ($body['data'] ?? $body) : [];
        $query    = $request->query->all();

        unset($query['action'], $query['pdk_action'], $query['path']);

        return array_replace($bodyData, $query);
    }

    /**
     * @param  array $data
     *
     * @return \MyParcelNL\Sdk\Model\Capabilities\CapabilitiesRequest
     */
    private function createCapabilitiesRequest(array $data): CapabilitiesRequest
    {
        $sender     = $data['sender'] ?? null;
        $country    = $data['country'] ?? $data['country_code'] ?? $data['countryCode'] ?? null;
        $shopId     = $data['shopId'] ?? $data['shop_id'] ?? null;
        
        if (!$shopId) {
            $shop   = AccountSettings::getShop();
            $shopId = $shop ? $shop->id : null;
        }
        
        $carrier    = $data['carrier'] ?? $data['carrier_id'] ?? null;
        $options    = $this->normalizeOptions($data['options'] ?? $data['shipment_options'] ?? null);
        $properties = $data['physicalProperties'] ?? $data['physical_properties'] ?? null;

        if (! $country && is_array($sender)) {
            $country = $sender['country_code'] ?? $sender['countryCode'] ?? null;
        }

        $request = CapabilitiesRequest::forCountry((string) $country);

        if ($shopId) {
            $request = $request->withShopId((int) $shopId);
        }

        if ($carrier) {
            $request = $request->withCarrier((string) $carrier);
        }

        if (is_array($sender)) {
            $request = $request->withSender($sender);
        }

        if ($options) {
            $request = $request->withOptions($options);
        }

        if (is_array($properties)) {
            $request = $request->withPhysicalProperties($properties);
        }

        return $request;
    }

    /**
     * @param  mixed $capabilitiesResponse
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createSymfonyResponse($capabilitiesResponse): Response
    {
        $statusCode = method_exists($capabilitiesResponse, 'getStatusCode')
            ? (int) $capabilitiesResponse->getStatusCode()
            : 200;

        if (method_exists($capabilitiesResponse, 'getRawResponse')) {
            $raw = $capabilitiesResponse->getRawResponse();

            if (is_string($raw)) {
                return new Response($raw, $statusCode, ['Content-Type' => 'application/json']);
            }

            if (is_array($raw)) {
                return new Response(json_encode($raw), $statusCode, ['Content-Type' => 'application/json']);
            }
        }

        if (method_exists($capabilitiesResponse, 'toArray')) {
            return new Response(
                json_encode($capabilitiesResponse->toArray()),
                $statusCode,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response(json_encode($capabilitiesResponse), $statusCode, ['Content-Type' => 'application/json']);
    }

    /**
     * @param  \MyParcelNL\Sdk\Services\Capabilities\CapabilitiesServiceInterface $service
     * @param  null|string                                                       $apiKey
     * @param  null|string                                                       $baseUrl
     *
     * @return void
     */
    private function configureService(CapabilitiesServiceInterface $service, ?string $apiKey, ?string $baseUrl): void
    {
        if ($apiKey && method_exists($service, 'setApiKey')) {
            $service->setApiKey($apiKey);
        }

        $userAgents = array_merge(
            (array) (Pdk::get('userAgent') ?? []),
            [
                'MyParcelNL-PDK' => Pdk::get('pdkVersion'),
                'php'            => PHP_VERSION,
            ]
        );

        if (method_exists($service, 'setUserAgents')) {
            $service->setUserAgents($userAgents);
        } elseif (method_exists($service, 'setUserAgent')) {
            foreach ($userAgents as $platform => $version) {
                $service->setUserAgent($platform, $version);
            }
        }

        if ($baseUrl && method_exists($service, 'setBaseUrl')) {
            $service->setBaseUrl($baseUrl);
        }
    }

    /**
     * @param  mixed $options
     *
     * @return array
     */
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

        return array_fill_keys($options, new \stdClass());
    }
}

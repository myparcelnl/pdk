<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Addresses;

use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\Api\Response\ValidateAddressResponse;
use MyParcelNL\Pdk\Api\Request\ProxyRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AddressesValidateAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Api\Service\AddressesApiService
     */
    private $apiService;

    /**
     * @param  \MyParcelNL\Pdk\Api\Service\AddressesApiService $apiService
     */
    public function __construct(AddressesApiService $apiService)
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
        $query = $request->query->all();

        // Ensure required parameters are present with correct format
        $queryParams = [
            'countryCode' => $query['cc'] ?? null,
            'postalCode'  => $query['postalCode'] ?? null,
            'houseNumber' => $query['houseNumber'] ?? null,
        ];

        // Filter out null values
        $queryParams = array_filter($queryParams, function ($value) {
            return $value !== null;
        });

        $proxyRequest = new ProxyRequest(
            'GET',
            '/validate',
            null,
            $queryParams
        );

        /** @var ValidateAddressResponse $response */
        $response = $this->apiService->doRequest($proxyRequest, ValidateAddressResponse::class);

        return new JsonResponse([
            'valid' => $response->isValid(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Addresses;

use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\Api\Response\ValidateAddressResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MyParcelNL\Pdk\Api\Request\Request as ApiRequest;
use MyParcelNL\Pdk\Api\Response\AddressResponse;

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
     * Build an outgoing request to the address microservice.
     * @param Request $incomingRequest
     * @return ApiRequest
     */
    public function buildRequest(Request $incomingRequest): ApiRequest
    {
        $query = $incomingRequest->query->all();

        // Ensure required parameters are present with correct format
        $queryParams = [
            'countryCode'       => $query['cc'] ?? null,
            'postalCode'        => $query['postalCode'] ?? null,
            'houseNumber'       => $query['houseNumber'] ?? null,
            'houseNumberSuffix' => $query['houseNumberSuffix'] ?? null,
            'city'              => $query['city'] ?? null,
            'region'            => $query['region'] ?? null,
            'street'            => $query['street'] ?? null,
            'validationType'    => $query['validationType'] ?? null,
        ];

        // Filter out null values
        $queryParams = array_filter($queryParams, function ($value) {
            return $value !== null;
        });

        return new ApiRequest([
            'method' => 'GET',
            'path' => '/validate',
            'parameters' => $queryParams
        ]);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        /** @var AddressResponse $response */
        $response = $this->apiService->doRequest($this->buildRequest($request), AddressResponse::class);
        return $response->getSymfonyResponse();
    }
}

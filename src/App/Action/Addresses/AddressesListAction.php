<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Addresses;

use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\Api\Request\Request as ApiRequest;
use MyParcelNL\Pdk\Api\Response\AddressResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AddressesListAction implements ActionInterface
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

    public function buildRequest(Request $incomingRequest): ApiRequest
    {
        $query = $incomingRequest->query->all();

        // Ensure required parameters are present with correct format
        $queryParams = [
            'countryCode' => $query['countryCode'] ?? null,
            'postalCode'  => $query['postalCode'] ?? null,
            'houseNumber' => $query['houseNumber'] ?? null,
            'query'       => $query['query'] ?? null,
            'limit'       => $query['limit'] ?? 5,
        ];

        // Filter out null values
        $queryParams = array_filter($queryParams, function ($value) {
            return $value !== null;
        });

        return new ApiRequest([
            'method' => 'GET',
            'path' => '/addresses',
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
        /**
         * @var AddressResponse $response
         */
        $response = $this->apiService->doRequest($this->buildRequest($request), AddressResponse::class);
        return $response->getSymfonyResponse();
    }
}

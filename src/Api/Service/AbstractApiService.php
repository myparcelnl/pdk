<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Response\ApiResponse;
use MyParcelNL\Pdk\Api\Response\ApiResponseInterface;
use MyParcelNL\Pdk\Base\Request\RequestInterface;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;

abstract class AbstractApiService implements ApiServiceInterface
{
    /**
     * @var null|string
     */
    protected $baseUrl;

    /**
     * @var \MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface
     */
    protected $clientAdapter;

    /**
     * @param  \MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface $clientAdapter
     */
    public function __construct(ClientAdapterInterface $clientAdapter)
    {
        $this->clientAdapter = $clientAdapter;
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Request\RequestInterface $request
     * @param  string                                        $responseClass
     *
     * @return \MyParcelNL\Pdk\Api\Response\ApiResponseInterface
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     */
    public function doRequest(
        RequestInterface $request,
        string           $responseClass = ApiResponse::class
    ): ApiResponseInterface {
        $uri        = $this->buildUri($request);
        $httpMethod = $request->getMethod();

        $options = [
            'headers' => $request->getHeaders() + $this->getHeaders(),
            'body'    => $request->getBody(),
        ];

        $response = $this->clientAdapter->doRequest($httpMethod, $uri, $options);

        DefaultLogger::debug('Request to MyParcel', [
            'uri'          => $uri,
            'method'       => $httpMethod,
            'options'      => $options,
            'responseCode' => $response->getStatusCode(),
            'body'         => $response->getBody(),
        ]);

        /** @var \MyParcelNL\Pdk\Api\Response\ApiResponseInterface $responseObject */
        $responseObject = new $responseClass($response);

        if ($responseObject->isErrorResponse()) {
            throw new ApiException($response);
        }

        return $responseObject;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl ?? Pdk::get('apiUrl');
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [];
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Request\RequestInterface $request
     *
     * @return string
     */
    protected function buildUri(RequestInterface $request): string
    {
        $url = implode('/', [
            trim($this->getBaseUrl(), '/'),
            trim($request->getPath(), '/'),
        ]);

        if (! empty($request->getQueryString())) {
            $url .= "?{$request->getQueryString()}";
        }

        return $url;
    }
}

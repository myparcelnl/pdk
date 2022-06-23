<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use GuzzleHttp\RequestOptions;
use MyParcelNL\Pdk\Account\Request\RequestInterface;
use MyParcelNL\Pdk\Api\Concern\ApiResponseInterface;
use MyParcelNL\Sdk\src\Exception\ApiException;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractApiService implements ApiServiceInterface
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @return string
     */
    abstract public function getBaseUrl(): string;

    /**
     * @param  \MyParcelNL\Pdk\Account\Request\RequestInterface $request
     * @param  string                                           $responseClass
     *
     * @return \MyParcelNL\Pdk\Api\Concern\ApiResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     */
    public function doRequest(
        RequestInterface $request,
        string           $responseClass
    ): ApiResponseInterface {
        $requestOptions = array_filter([
            RequestOptions::HEADERS => $this->getRequestHeaders(),
            RequestOptions::BODY    => $request->getBody(),
        ]);

        $requestOptions[RequestOptions::HTTP_ERRORS] = false;

        $response = $this->httpClient->request(
            $request->getHttpMethod(),
            $this->buildUri($request),
            $requestOptions
        );

        /** @var \MyParcelNL\Pdk\Api\Concern\ApiResponseInterface $responseObject */
        $responseObject = new $responseClass($response);

        if ($responseObject->isErrorResponse()) {
            throw new ApiException('External request failed.');
        }

        return $responseObject;
    }

    /**
     * @param  \MyParcelNL\Pdk\Account\Request\RequestInterface $request
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

    /**
     * @return array
     */
    protected function getRequestHeaders(): array
    {
        return [];
    }
}

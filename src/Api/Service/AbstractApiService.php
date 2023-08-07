<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Api\Contract\ApiResponseInterface;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Request\RequestInterface;
use MyParcelNL\Pdk\Api\Response\ApiResponse;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use RuntimeException;
use Throwable;

abstract class AbstractApiService implements ApiServiceInterface
{
    /**
     * @var null|string
     */
    protected $baseUrl;

    /**
     * @var \MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface
     */
    protected $clientAdapter;

    /**
     * @param  \MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface $clientAdapter
     */
    public function __construct(ClientAdapterInterface $clientAdapter)
    {
        $this->clientAdapter = $clientAdapter;
    }

    /**
     * @param  \MyParcelNL\Pdk\Api\Request\RequestInterface $request
     * @param  class-string<ApiResponseInterface>           $responseClass
     *
     * @return \MyParcelNL\Pdk\Api\Contract\ApiResponseInterface
     * @throws \MyParcelNL\Pdk\Api\Exception\ApiException
     */
    public function doRequest(
        RequestInterface $request,
        string           $responseClass = ApiResponse::class
    ): ApiResponseInterface {
        $uri    = $this->buildUri($request);
        $method = $request->getMethod();

        $options = [
            'headers' => $request->getHeaders() + $this->getHeaders(),
            'body'    => $request->getBody(),
        ];

        $logContext = [
            'uri'     => $uri,
            'method'  => $method,
            'headers' => $options['headers'],
            'body'    => $options['body'] ? json_decode($options['body'], true) : null,
        ];

        Logger::debug('Sending request to MyParcel', $logContext);

        try {
            $response = $this->clientAdapter->doRequest($method, $uri, $options);
        } catch (Throwable $e) {
            Logger::error(
                'Error sending request to MyParcel',
                ['error' => $e->getMessage()] + $logContext
            );
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        /** @var \MyParcelNL\Pdk\Api\Contract\ApiResponseInterface $responseObject */
        $responseObject = new $responseClass($response);

        if ($responseObject->isErrorResponse()) {
            Logger::error(
                'Received an error response from MyParcel',
                [
                    'code'   => $response->getStatusCode(),
                    'errors' => $responseObject->getErrors(),
                ] + $logContext
            );
            throw new ApiException($response);
        }

        Logger::debug('Received response from MyParcel', compact('response'));

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
     * @param  \MyParcelNL\Pdk\Api\Request\RequestInterface $request
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

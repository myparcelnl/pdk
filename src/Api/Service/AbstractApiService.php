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

/**
 * Abstract base class for API services
 */
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

        $logContext = $this->createLogContext($uri, $method, $options);
        $response   = null;

        try {
            $response = $this->clientAdapter->doRequest($method, $uri, $options);
        } catch (Throwable $e) {
            Logger::error(
                'An exception was thrown while sending request',
                array_replace($logContext, ['error' => $e->getMessage()])
            );

            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        /** @var \MyParcelNL\Pdk\Api\Contract\ApiResponseInterface $responseObject */
        $responseObject = new $responseClass($response);
        $body           = $responseObject->getBody();

        $logContext['response'] = [
            'code' => $responseObject->getStatusCode(),
            'body' => $body ? json_decode($body, true) : null,
        ];

        if ($responseObject->isErrorResponse()) {
            Logger::error('Received an error response', $logContext);

            throw new ApiException($response);
        }

        Logger::debug('Successfully sent request', $logContext);

        return $responseObject;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        // Check if there's a session-specific base URL set
        if ($this->baseUrl) {
            return $this->baseUrl;
        }

        // Check if we're in acceptance mode via account settings
        try {
            $accountSettings = Pdk::get('accountSettings');
            if ($accountSettings && $accountSettings->isAcceptance()) {
                return \MyParcelNL\Pdk\Base\Config::API_URL_ACCEPTANCE;
            }
        } catch (\Throwable $e) {
            // Fall back to cache file check for backward compatibility
            $cacheFile = sys_get_temp_dir() . \MyParcelNL\Pdk\Base\Config::ACCEPTANCE_CACHE_FILE;
            if (file_exists($cacheFile)) {
                return \MyParcelNL\Pdk\Base\Config::API_URL_ACCEPTANCE;
            }
        }

        // Fall back to default API URL from config
        return Pdk::get('apiUrl');
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [];
    }

    /**
     * Check if currently connected to acceptance environment
     * This method is kept for backward compatibility but should be replaced
     * with checking AccountGeneralSettings->isAcceptance()
     *
     * @return bool
     */
    public function isConnectedToAcceptance(): bool
    {
        // Check if the current base URL is the acceptance URL
        return $this->getBaseUrl() === \MyParcelNL\Pdk\Base\Config::API_URL_ACCEPTANCE;
    }

    /**
     * @param  string $baseUrl
     *
     * @return ApiServiceInterface
     */
    public function setBaseUrl(string $baseUrl): ApiServiceInterface
    {
        $this->baseUrl = $baseUrl;
        return $this;
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

    /**
     * @param  string $uri
     * @param  string $method
     * @param  array  $options
     *
     * @return array
     */
    private function createLogContext(string $uri, string $method, array $options): array
    {
        $headers = array_combine(array_map('strtolower', array_keys($options['headers'])), $options['headers']);

        // Obfuscate sensitive headers
        foreach (['authorization', 'x-api-key', 'api-key'] as $sensitiveHeader) {
            if (isset($headers[$sensitiveHeader])) {
                $headers[$sensitiveHeader] = '***';
            }
        }

        return [
            'request' => [
                'uri'     => $uri,
                'method'  => $method,
                'headers' => $headers,
                'body'    => $options['body'] ? json_decode($options['body'], true) : null,
            ],
        ];
    }
}

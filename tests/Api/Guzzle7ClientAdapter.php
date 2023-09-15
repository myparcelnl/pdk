<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Api\Response\ClientResponse;

class Guzzle7ClientAdapter implements ClientAdapterInterface
{
    public function __construct(private Client $client)
    {
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function doRequest(string $httpMethod, string $uri, array $options = []): ClientResponseInterface
    {
        $method = strtolower($httpMethod);

        $requestOptions = array_filter([
            RequestOptions::HEADERS => $options['headers'] ?? null,
            RequestOptions::BODY    => $options['body'] ?? null,
        ]);

        $requestOptions[RequestOptions::HTTP_ERRORS] = false;

        $response     = $this->client->request($method, $uri, $requestOptions);
        $responseBody = $response->getBody();

        $body = $responseBody->isReadable()
            ? $responseBody->getContents()
            : null;

        return new ClientResponse($body, $response->getStatusCode());
    }

    /**
     * @return $this
     */
    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }
}

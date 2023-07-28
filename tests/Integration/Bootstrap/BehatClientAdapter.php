<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Bootstrap;

use GuzzleHttp\Client;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Api\Response\ClientResponse;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Tests\Api\Guzzle7ClientAdapter;
use MyParcelNL\Sdk\src\Support\Str;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class BehatClientAdapter extends Guzzle7ClientAdapter
{
    /**
     * @var \MyParcelNL\Pdk\Tests\Integration\Bootstrap\BehatConfig
     */
    private $config;

    /**
     * @param  \GuzzleHttp\Client                            $client
     * @param  \MyParcelNL\Pdk\Base\Contract\ConfigInterface $config
     */
    public function __construct(Client $client, ConfigInterface $config)
    {
        parent::__construct($client);
        $this->config = $config;
    }

    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function doRequest(string $httpMethod, string $uri, array $options = []): ClientResponseInterface
    {
        if (Str::startsWith($uri, '/PDK')) {
            return $this->getInternalResponse();
        }

        return $this->getExternalResponse($httpMethod, $uri, $options);
    }

    /**
     * @return null|\MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     */
    public function getMockedResponse(): ?ClientResponseInterface
    {
        $examples = $this->config->get('Examples');
        $request  = Request::createFromGlobals();

        foreach ($examples as $example) {
            if (! $this->validateExample($example)) {
                continue;
            }

            if ($example['match']($request)) {
                return $example['response']($request);
            }
        }

        return null;
    }

    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRealResponse(string $httpMethod, string $uri, array $options): ClientResponseInterface
    {
        $realResponse = parent::doRequest($httpMethod, $uri, $options);

        $this->config->writeExample($httpMethod, $uri, $options, $realResponse);

        return $realResponse;
    }

    /**
     * @return \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     */
    protected function getInternalResponse(): ClientResponseInterface
    {
        $request = Request::createFromGlobals();

        $response = Actions::execute($request);

        return $this->toClientResponse($response);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Response $response
     *
     * @return \MyParcelNL\Pdk\Api\Response\ClientResponse
     */
    protected function toClientResponse(Response $response): ClientResponse
    {
        return new ClientResponse($response->getContent(), $response->getStatusCode(), $response->headers->all());
    }

    /**
     * @param  mixed $example
     *
     * @return bool
     */
    protected function validateExample($example): bool
    {
        return is_array($example)
            && array_key_exists('match', $example)
            && array_key_exists('response', $example)
            && is_callable($example['match'])
            && is_callable($example['response']);
    }

    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getExternalResponse(string $httpMethod, string $uri, array $options): ClientResponseInterface
    {
        return $this->getMockedResponse() ?? $this->getRealResponse($httpMethod, $uri, $options);
    }
}

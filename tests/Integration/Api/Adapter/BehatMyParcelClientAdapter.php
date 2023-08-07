<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Api\Adapter;

use GuzzleHttp\Client;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Guzzle7ClientAdapter;
use MyParcelNL\Pdk\Tests\Integration\Adapter\SymfonyRequestAdapter;
use MyParcelNL\Pdk\Tests\Integration\Exception\NoExampleException;

final class BehatMyParcelClientAdapter extends Guzzle7ClientAdapter
{
    /**
     * @var \MyParcelNL\Pdk\Tests\Integration\Base\BehatConfig
     */
    private $config;

    /**
     * @var \MyParcelNL\Pdk\Tests\Integration\Adapter\SymfonyRequestAdapter
     */
    private $requestAdapter;

    /**
     * @param  \GuzzleHttp\Client                                              $client
     * @param  \MyParcelNL\Pdk\Base\Contract\ConfigInterface                   $config
     * @param  \MyParcelNL\Pdk\Tests\Integration\Adapter\SymfonyRequestAdapter $requestAdapter
     */
    public function __construct(Client $client, ConfigInterface $config, SymfonyRequestAdapter $requestAdapter)
    {
        parent::__construct($client);
        $this->config         = $config;
        $this->requestAdapter = $requestAdapter;
    }

    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return void
     * @throws \Brick\VarExporter\ExportException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createExampleFromRealResponse(string $httpMethod, string $uri, array $options): void
    {
        $realResponse = parent::doRequest($httpMethod, $uri, $options);

        $this->config->writeExample($httpMethod, $uri, $options, $realResponse);
    }

    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     * @throws \Brick\VarExporter\ExportException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MyParcelNL\Pdk\Tests\Integration\Exception\NoExampleException
     */
    public function doRequest(string $httpMethod, string $uri, array $options = []): ClientResponseInterface
    {
        $mockedResponse = $this->getMockedResponse($httpMethod, $uri, $options);

        if ($mockedResponse) {
            return $mockedResponse;
        }

        $this->createExampleFromRealResponse($httpMethod, $uri, $options);

        throw new NoExampleException(
            sprintf(
                'No example found for %s request to %s. A new example has been generated. Please run the test again.',
                $httpMethod,
                $uri
            )
        );
    }

    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return null|\MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     */
    public function getMockedResponse(string $httpMethod, string $uri, array $options): ?ClientResponseInterface
    {
        $examples = $this->config->get(Pdk::get('behatExamplesDir'));
        $request  = $this->requestAdapter->fromParts($httpMethod, $uri, $options);

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
}

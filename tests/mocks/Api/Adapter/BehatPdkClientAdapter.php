<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Adapter;

use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Response\BehatClientResponse;
use MyParcelNL\Pdk\Facade\Actions;
use Symfony\Component\HttpFoundation\Response;

final class BehatPdkClientAdapter implements ClientAdapterInterface
{
    /**
     * @var \MyParcelNL\Pdk\Api\Adapter\SymfonyRequestAdapter
     */
    private $requestAdapter;

    /**
     * @param  \MyParcelNL\Pdk\Api\Adapter\SymfonyRequestAdapter $requestAdapter
     */
    public function __construct(SymfonyRequestAdapter $requestAdapter)
    {
        $this->requestAdapter = $requestAdapter;
    }

    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function doRequest(string $httpMethod, string $uri, array $options = []): ClientResponseInterface
    {
        $request = $this->requestAdapter->fromParts($httpMethod, $uri, $options);

        try {
            $response = Actions::execute($request);
        } catch (ApiException $e) {
            return $e->getResponse();
        }

        return $this->toClientResponse($response);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Response $response
     *
     * @return \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     */
    protected function toClientResponse(Response $response): ClientResponseInterface
    {
        return new BehatClientResponse($response->getContent(), $response->getStatusCode(), $response->headers->all());
    }
}

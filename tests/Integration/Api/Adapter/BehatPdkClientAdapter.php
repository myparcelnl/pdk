<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Api\Adapter;

use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Tests\Integration\Adapter\SymfonyRequestAdapter;
use MyParcelNL\Pdk\Tests\Integration\Api\Response\BehatClientResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class BehatPdkClientAdapter implements ClientAdapterInterface
{
    public function __construct(private SymfonyRequestAdapter $requestAdapter)
    {
    }

    /**
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

    protected function toClientResponse(Response $response): ClientResponseInterface
    {
        return new BehatClientResponse($response->getContent(), $response->getStatusCode(), $response->headers->all());
    }
}

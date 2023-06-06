<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Contract;

interface ClientAdapterInterface
{
    /**
     * Execute a request.
     *
     * @see \MyParcelNL\Pdk\Tests\Api\Guzzle7ClientAdapter for an example implementation.
     */
    public function doRequest(string $httpMethod, string $uri, array $options = []): ClientResponseInterface;
}

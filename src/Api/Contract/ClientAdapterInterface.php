<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Contract;

interface ClientAdapterInterface
{
    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     */
    public function doRequest(string $httpMethod, string $uri, array $options = []): ClientResponseInterface;
}

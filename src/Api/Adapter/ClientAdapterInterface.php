<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Adapter;

use MyParcelNL\Pdk\Api\Response\ClientResponseInterface;

interface ClientAdapterInterface
{
    /**
     * @param  string $httpMethod
     * @param  string $uri
     * @param  array  $options
     *
     * @return \MyParcelNL\Pdk\Api\Response\ClientResponseInterface
     */
    public function doRequest(string $httpMethod, string $uri, array $options = []): ClientResponseInterface;
}

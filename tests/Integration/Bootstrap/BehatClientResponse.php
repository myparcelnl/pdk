<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Bootstrap;

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Api\Response\ClientResponse;

final class BehatClientResponse extends ClientResponse
{
    /**
     * @param  \GuzzleHttp\Psr7\Response $response
     *
     * @return self
     */
    public static function create(Response $response): self
    {
        return new self(
            $response->getBody()
                ->getContents(),
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Api\Response;

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Api\Response\ClientResponse;

final class BehatClientResponse extends ClientResponse
{
    /**
     * @param  string|array $body
     */
    public function __construct($body, int $statusCode, array $headers = [])
    {
        if (! is_string($body)) {
            $body = json_encode($body, JSON_THROW_ON_ERROR);
        }

        parent::__construct($body, $statusCode, $headers);
    }

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

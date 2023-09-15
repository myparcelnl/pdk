<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;

class ClientResponse implements ClientResponseInterface
{
    /**
     * @param  null|string $body
     */
    public function __construct(
        private readonly ?string $body,
        private readonly int     $statusCode,
        private readonly array   $headers = []
    ) {
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

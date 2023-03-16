<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;

class ClientResponse implements ClientResponseInterface
{
    /**
     * @var null|string
     */
    private $body;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @param  null|string $body
     * @param  int         $statusCode
     * @param  array       $headers
     */
    public function __construct(?string $body, int $statusCode, array $headers = [])
    {
        $this->body       = $body;
        $this->statusCode = $statusCode;
        $this->headers    = $headers;
    }

    /**
     * @return null|string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

class ClientResponse implements ClientResponseInterface
{
    /**
     * @var null|string
     */
    private $body;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @param  null|string $body
     * @param  int         $statusCode
     */
    public function __construct(?string $body, int $statusCode)
    {
        $this->body       = $body;
        $this->statusCode = $statusCode;
    }

    /**
     * @return null|string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

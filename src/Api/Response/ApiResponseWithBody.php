<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;

class ApiResponseWithBody extends ApiResponse
{
    private ?string $body = null;

    public function __construct(ClientResponseInterface $response)
    {
        parent::__construct($response);
        $this->body = $response->getBody();

        if ($this->body && $this->getStatusCode() >= 300) {
            $this->parseErrors();
        }

        if ($this->isOkResponse()) {
            $this->parseResponseBody();
        }
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function parseResponseBody(): void
    {
    }
}

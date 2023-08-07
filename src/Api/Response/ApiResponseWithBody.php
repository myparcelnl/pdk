<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;

class ApiResponseWithBody extends ApiResponse
{
    /**
     * @var null|string
     */
    private $body;

    /**
     * @param  \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface $response
     */
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

    /**
     * @return null|string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @return void
     * @codeCoverageIgnore
     */
    protected function parseResponseBody(): void
    {
    }
}

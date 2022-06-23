<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use Psr\Http\Message\ResponseInterface;

abstract class AbstractApiResponseWithBody extends AbstractApiResponse
{
    /**
     * @var string
     */
    private $body;

    /**
     * @param  \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);
        $this->body = (string) $response->getBody();

        if ($this->isUnprocessableEntity()) {
            $this->parseErrors($this->getBody());
        }

        if ($this->isOkResponse()) {
            $this->parseResponseBody((string) $response->getBody());
        }
    }

    /**
     * @param  string $body
     */
    abstract protected function parseResponseBody(string $body): void;

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
}

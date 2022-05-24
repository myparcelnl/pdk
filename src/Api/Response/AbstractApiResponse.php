<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use Psr\Http\Message\ResponseInterface;

abstract class AbstractApiResponse extends AbstractApiResponseWithoutBody
{
    /**
     * @param  \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);

        if ($this->isOkResponse()) {
            $this->parseResponseBody((string) $response->getBody());
        }
    }

    /**
     * @param  string $body
     */
    abstract protected function parseResponseBody(string $body): void;
}

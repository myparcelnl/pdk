<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiResponse implements ApiResponseInterface
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var \MyParcelNL\Pdk\Api\Response\ClientResponseInterface
     */
    private $response;

    /**
     * @param  \MyParcelNL\Pdk\Api\Response\ClientResponseInterface $response
     */
    public function __construct(ClientResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return bool
     */
    public function isErrorResponse(): bool
    {
        return $this->getStatusCode() < Response::HTTP_OK || $this->getStatusCode() >= 299;
    }

    /**
     * @return bool
     */
    public function isOkResponse(): bool
    {
        return ! $this->isErrorResponse();
    }

    /**
     * @return void
     */
    protected function parseErrors(): void
    {
        $this->errors = [new ApiException($this->response)];
    }
}

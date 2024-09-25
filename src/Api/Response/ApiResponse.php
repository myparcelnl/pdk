<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Contract\ApiResponseInterface;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse implements ApiResponseInterface
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface
     */
    private $response;

    /**
     * @param  \MyParcelNL\Pdk\Api\Contract\ClientResponseInterface $response
     */
    public function __construct(ClientResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return null|string
     */
    public function getBody(): ?string
    {
        return null;
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
     * @param  null|array $item
     *
     * @return null|array
     */
    protected function filter(?array $item): ?array
    {
        return array_filter($item ?? []) ?: null;
    }

    /**
     * @return void
     */
    protected function parseErrors(): void
    {
        $this->errors = [new ApiException($this->response)];
    }
}

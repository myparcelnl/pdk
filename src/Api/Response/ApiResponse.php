<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Contract\ApiResponseInterface;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse implements ApiResponseInterface
{
    private array $errors = [];

    public function __construct(private readonly ClientResponseInterface $response)
    {
    }

    public function getBody(): ?string
    {
        return null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function isErrorResponse(): bool
    {
        return $this->getStatusCode() < Response::HTTP_OK || $this->getStatusCode() >= 299;
    }

    public function isOkResponse(): bool
    {
        return ! $this->isErrorResponse();
    }

    protected function parseErrors(): void
    {
        $this->errors = [new ApiException($this->response)];
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Contract;

interface ApiResponseInterface
{
    public function __construct(ClientResponseInterface $response);

    public function getBody(): ?string;

    public function getErrors(): array;

    public function getStatusCode(): int;

    public function isErrorResponse(): bool;

    public function isOkResponse(): bool;
}

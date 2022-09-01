<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

interface ApiResponseInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Api\Response\ClientResponseInterface $response
     */
    public function __construct(ClientResponseInterface $response);

    /**
     * @return null|string
     */
    public function getBody(): ?string;

    /**
     * @return array
     */
    public function getErrors(): array;

    /**
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * @return bool
     */
    public function isErrorResponse(): bool;

    /**
     * @return bool
     */
    public function isOkResponse(): bool;
}

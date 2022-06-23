<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Concern;

use Psr\Http\Message\ResponseInterface;

interface ApiResponseInterface
{
    /**
     * @param  \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(ResponseInterface $response);

    /**
     * @return string
     */
    public function getBody(): string;

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

    /**
     * @return bool
     */
    public function isUnprocessableEntity(): bool;
}

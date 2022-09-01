<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

interface ClientResponseInterface
{
    /**
     * @return string|null
     */
    public function getBody(): ?string;

    /**
     * @return int
     */
    public function getStatusCode(): int;
}

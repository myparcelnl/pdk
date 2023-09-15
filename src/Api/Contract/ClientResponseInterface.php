<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Contract;

interface ClientResponseInterface
{
    public function getBody(): ?string;

    public function getStatusCode(): int;
}

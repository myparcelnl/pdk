<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Base\Support\Collection;

class PostIdsResponse extends ApiResponseWithBody
{
    private ?Collection $ids = null;

    public function getIds(): Collection
    {
        return $this->ids;
    }

    protected function parseResponseBody(): void
    {
        $parsedBody = json_decode($this->getBody(), true);
        $this->ids  = new Collection($parsedBody['data']['ids'] ?? []);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Base\Support\Collection;

class PostIdsResponse extends ApiResponseWithBody
{
    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    private $ids;

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
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

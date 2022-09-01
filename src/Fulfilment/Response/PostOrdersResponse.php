<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Response;

use MyParcelNL\Pdk\Api\Response\AbstractApiResponseWithBody;
use MyParcelNL\Pdk\Base\Support\Collection;

class PostOrdersResponse extends AbstractApiResponseWithBody
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

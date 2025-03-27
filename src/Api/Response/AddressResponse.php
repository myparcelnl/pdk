<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;

class AddressResponse extends ApiResponseWithBody
{
    /**
     * @var array
     */
    private $results = [];

    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @return void
     */
    protected function parseResponseBody(): void
    {
        $data = json_decode($this->getBody() ?? '{"results":[]}', true);
        $this->results = $data['results'] ?? [];
    }
} 
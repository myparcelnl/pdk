<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

/**
 * Response for address list requests
 */
class ListAddressResponse extends AddressResponse
{
    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->data['results'] ?? [];
    }

    /**
     * @return void
     */
    protected function parseResponseBody(): void
    {
        parent::parseResponseBody();
        $this->data = ['results' => $this->data['results'] ?? []];
    }
}
